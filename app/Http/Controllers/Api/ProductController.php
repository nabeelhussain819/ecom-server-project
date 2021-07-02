<?php

namespace App\Http\Controllers\Api;

use App\Helpers\GuidHelper;
use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductsAttribute;
use App\Models\ProductsCategories;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        return ProductsCategories::with('products', 'category')
            ->whereHas('products', function ($query) {
                $query->where('active', true);
            })->get();
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


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $product = new Product();

        //temporary 1, for testing
        $request['user_id'] = 1;
        $product->fill($request->all());
        $product->save();
        $productCategories = new ProductsCategories($request->all());
        $product->categories()->saveMany([$productCategories]);

        $attributes = [];
        foreach ($request->get('attributes', []) as $attribute) {
            $attributes[] = [
                'attribute_id' => $attribute['id'],
                'product_id' => $product->id,
                'value' => $attribute['value']
            ];
        }

        ProductsAttribute::insert($attributes);

        return $this->genericResponse(true, 'Product Created', 200, ['product' => $product->withCategories()]);
    }

    /**
     * @param Product $product
     * @return Product
     */
    public function show(Product $product)
    {
        return $product->withCategories()->withProductsAttributes();
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
        $product->fill($request->all())->update();

        $attributes = ($postedAttributes = $request->get('attributes')) ? array_combine(array_column($postedAttributes, 'id'), array_column($postedAttributes, 'value')) : [];
        // @TODO: create relations to avoid where query
        ProductsAttribute::where('product_id', $product->id)
            ->get()
            ->each(function (ProductsAttribute $attribute) use ($attributes) {
                $attribute->value = $attributes[$attribute->attribute_id];
                $attribute->save();
            });
        //ProductsCategories::where('product_id',$product->id)->update(['category_id' => $request->category_id]);
        return $this->genericResponse(true, "$product->name Updated", 200, ['product' => $product->withCategories()]);
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
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $guid = GuidHelper::getGuid();
        $path = User::getUploadPath() . Media::PRODUCT_IMAGES;
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
        ];
    }
}
