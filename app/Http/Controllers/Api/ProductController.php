<?php

namespace App\Http\Controllers\Api;

use App\Events\OfferMade;
use App\Helpers\GuidHelper;
use App\Helpers\StringHelper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Media;
use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductsAttribute;
use App\Models\User;
use App\Models\Message;
use App\Scopes\ActiveScope;
use App\Scopes\SoldScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Stripe\StripeClient;

class ProductController extends Controller
{
    //
    public function index(Request $request)
    {
        // why Product Categories whynot products ? @todo refactor it make it simple
//        return ProductsCategories::with('products', 'categories')
//            ->whereHas('products', function ($query) {
//                $query->where('active', true);
//            })->get();


        return Product::where('active', true)
            ->where($this->applyFilters($request))
            ->orderByDesc('featured')
            ->paginate($this->pageSize);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function self()
    {
        return Product::where('user_id', \Auth::user()->id)
            ->with(['category', 'media'])
            ->withoutGlobalScope(ActiveScope::class)
            ->withoutGlobalScope(SoldScope::class)
            ->paginate($this->pageSize);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $product = new Product();

            //temporary 1, for testing
            $request['user_id'] = \Auth::user()->id;
            $request['status'] = "DRAFT";
            $product->fill($request->all());
            $product->save();

            //@todo inherit attribute functionality
            foreach ($request->get('attributes', []) as $attribute) {
                $data = [
                    'attribute_id' => $attribute['id'],
                    'product_id' => $product->id,
                    'value' => $attribute['value']
                ];

                $productAttribute = new ProductsAttribute($data);
                $productAttribute->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $this->genericResponse(true, 'Product Created', 200, ['product' => $product->withCategory()]);
    }

    /**
     * @param Product $product
     * @return Product
     */
    public function show(Product $product)
    {
        $product->price = $product->getPrice();

        return $product->withCategory()
            ->withProductsAttributes()
            ->appendDetailAttribute()
            ->withUser();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        DB::beginTransaction();
        try {
            $product->fill($request->all())->update();

            $attributes = ($postedAttributes = $request->get('attributes')) ? array_combine(array_column($postedAttributes, 'id'), array_column($postedAttributes, 'value')) : [];
            if (!empty($attributes)) {

                // @TODO: create relations to avoid where query
                ProductsAttribute::where('product_id', $product->id)
                    ->get()
                    ->each(function (ProductsAttribute $attribute) use ($attributes) {
                        $attribute->value = $attributes[$attribute->attribute_id];
                        $attribute->save();
                    });
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $this->genericResponse(true, "$product->name Updated", 200, ['product' => $product->withCategory()->withProductsAttributes()
            ->appendDetailAttribute()]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Product::destroy($id);
        return response()->json(['message' => 'Product Deleted Successfully'], 200);
    }

    public function media(Product $product, Request $request)
    {
        return $product->images();
    }

    /**
     * @param Product $product
     * @param Request $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function upload(Product $product, Request $request)
    {
        return DB::transaction(function () use (&$request, &$product) {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $guid = GuidHelper::getGuid();
            $path = User::getUploadPath() . StringHelper::trimLower(Media::PRODUCT_IMAGES);
            $name = "{$path}/{$guid}.{$extension}";
            $media = new Media();
            $media->fill([
                'name' => $name,
                'extension' => $extension,
                'type' => Media::PRODUCT_IMAGES,
                'user_id' => \Auth::user()->id,
                'product_id' => $product->id,
                'active' => true,
            ]);

            $media->save();

            Storage::putFileAs(
                'public/' . $path, $request->file('file'), "{$guid}.{$extension}"
            );

            return [
                'uid' => $media->id,
                'name' => $media->url,
                'status' => 'done',
                'url' => $media->url,
                'guid' => $media->guid
            ];
        });
    }

    public function search(Request $request)
    {
        $products = Product::where($this->applyFilters($request))->where('name', 'LIKE', "%{$request->get('query')}%")
            ->when($request->get('category_id'), function (Builder $builder, $category) use ($request) {
                $builder->where('category_id', $category)
                    ->when(json_decode($request->get('filters'), true), function (Builder $builder, $filters) {
                        $having = [];

                        foreach ($filters as $id => $value) {
                            if (is_bool($value)) {
                                $value = $value ? 'true' : 'false';
                            }

                            if (is_array($value)) {
                                $value = implode('","', $value);
                                $having[] = "sum(case when products_attributes.attribute_id = $id and json_overlaps(products_attributes.value, '[\"$value\"]') then 1 else 0 end) > 0";
                            } else {
                                $having[] = "sum(case when products_attributes.attribute_id = $id and json_contains(products_attributes.value, '\"$value\"') then 1 else 0 end) > 0";
                            }
                        }

                        $having = implode(' and ', $having);
                        $builder->whereRaw("
                            id in
                            (select products.id
                            from products
                            inner join products_attributes on products.id = products_attributes.product_id
                            group by products.id
                            having $having)
                        ");
                    });
            })
            ->distinct()
            ->orderByDesc('featured')
            ->get();

        $categories = Category::when($request->get('category_id'), function (Builder $builder, $category) {
            $builder->where('id', $category)
                ->with('attributes');
        })
            ->where('type', Category::PRODUCT)
            ->get();

        return [
            'results' => $products,
            'categories' => $categories
        ];
    }

    /**
     * Saved user products
     * @param Product $product
     * @param Request $request
     */
    public function Saved(Product $product, Request $request)
    {
        $product->attachOrDetachSaved();
    }

    /*
     * @param Product $product
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function offer(Product $product, Request $request)
    {
        $offer = $request->get('offer');

        // optimize move this into the request
        if ($offer == $product->price && $offer >= $product->price) {
            throw new \Exception('Make an offer lesser than the product price.');
        }

        $sender = Auth::user();
        $recipient = $product->user;

        if ($sender->id === $recipient->id) {
            throw new \Exception('Unable to make an offer on your own product');
        }

        $message = new Message();
        $message->sender_id = $sender->id;
        $message->recipient_id = $recipient->id;
        $message->data = $sender->name . ' has offered you Rs. ' . $offer . ' for ' . $product->name;
        $message->notifiable_id = $product->id;
        $message->notifiable_type = Product::class;
        $message->save();

        Offer::request($product, $offer);

        OfferMade::trigger($recipient);

        return $this->genericResponse(true, 'Offer made successfully.');
    }

    public function getSaved()
    {
        if (Auth::check()) {
            $user = User::where('id', Auth::user()->id)->with('savedProducts')->first();
            return $user->savedProducts;
        }
    }

    public function deleteMedia(Media $media)
    {
        if (Auth::user()->id == $media->user_id) {
            Storage::delete($media->name);
            $media->delete();

        }

    }

    public function getBuyingOffers()
    {
        $user = Auth::user();
        return $user->buyingOffers()->with(["product" => function (BelongsTo $hasMany) {
            $hasMany->select(Product::defaultSelect());
        }])->get();
    }

    public function getSellingOffers()
    {
        $user = Auth::user();
        return $user->sellingOffers()->with(["product" => function (BelongsTo $hasMany) {
            $hasMany->select(Product::defaultSelect());
        }])->get();
    }

    public function feature(Product $product, Request $request)
    {
        $stripe = new StripeClient(env('STRIPE_SK'));
        $paymentIntent = $stripe->paymentIntents->retrieve($request->get('payment_intent'));

        $days = $request->get('days');
        if ($paymentIntent->id === $request->get('payment_intent') &&
            $paymentIntent->status === 'succeeded' &&
            $paymentIntent->amount === (Product::getFeaturedPrice($days) * 100)) {
            $product->featured = true;
            $product->featured_until = Carbon::today()->addDays($days);
            $product->update();
        }

        return $product;
    }

    public function hire(Product $product, Request $request)
    {
        $stripe = new StripeClient(env('STRIPE_SK'));
        $paymentIntent = $stripe->paymentIntents->retrieve($request->get('payment_intent'));

        $days = $request->get('days');
        if ($paymentIntent->id === $request->get('payment_intent') &&
            $paymentIntent->status === 'succeeded' &&
            $paymentIntent->amount === (Product::getHirePrice($days) * 100)) {
            $product->hired = true;
            $product->hired_until = Carbon::today()->addDays($days);
            $product->update();
        }

        return $product;
    }
}
