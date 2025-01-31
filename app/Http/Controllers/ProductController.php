<?php

namespace App\Http\Controllers;

use App\Helpers\GuidHelper;
use App\Helpers\StringHelper;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductsAttribute;
use App\Models\ProductsCategories;
use App\Scopes\ActiveScope;
use App\Scopes\SoldScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\isEmpty;

class ProductController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return view('products.index', [
            'products' => Product::where('active', true)->paginate(10)
        ]);
    }

    public function inActive()
    {

        $inActiveProduct = ProductsCategories::with('products')
            ->whereHas('products', function ($query) {
                $query->where('active', false);
            })->orderBy('created_at', 'ASC')->paginate(10);
        return view('products.in-active', ['products' => $inActiveProduct]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('products.create', ['categories' => Category::where('active', true)->with('attributes')->get()]);
    }

    public function search(Request $request)
    {

        $products = Product::withoutGlobalScope(ActiveScope::class)
            ->withoutGlobalScope(SoldScope::class)->paginate(15);
        return view('products.index', ['products' => $products]);
    }

    public function searchInActive(Request $request)
    {

        $search = $request->get('search');
        $products = ProductsCategories::with('products')
            ->whereHas('products', function ($query) use ($search) {
                $query->where('active', false)->where('name', 'like', '%' . $search . '%');
            })->withoutGlobalScope(ActiveScope::class)
            ->withoutGlobalScope(SoldScope::class)->paginate(10);
        return view('products.in-active', ['products' => $products]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        DB::transaction(function () use ($request) {
            $product = new Product();
            $product->guid = GuidHelper::getGuid();
            $request['user_id'] = auth()->user()->getAuthIdentifier();
            $product->fill($request->all())->save();

            $attributes = [];
            foreach ($request->get('attributes', []) as $id => $value) {
                $attributes[] = [
                    'attribute_id' => $id,
                    'product_id' => $product->id,
                    'value' => $value
                ];
            }

            ProductsAttribute::insert($attributes);
        });
        return redirect('admin/products')->with('success', 'Product Added');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        return view('products.edit', [
            'product' => Product::with('category')->withoutGlobalScope(ActiveScope::class)
                ->withoutGlobalScope(SoldScope::class)->findOrFail($id),
            'category' => Category::where('active', true)->get()
        ]);
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

        if ($request->get('activateOne') == "activateOnlyOne") {
            $product->update(['active' => !empty($request->get('checkbox'))]);
            return back()->with('success', "{$product->name} Status Changed Successfully.");
        } else {
            $product->fill($request->all())->update();
            //  ProductsCategories::where('product_id', $product->id)->update(['category_id' => $request->category_id]);

            $attributes = $request->get('attributes', []);
            // @TODO: create relations to avoid where query
            ProductsAttribute::where('product_id', $product->id)
                ->get()
                ->each(function (ProductsAttribute $attribute) use ($attributes) {
                    $attribute->value = $attributes[$attribute->attribute_id];
                    $attribute->save();
                });

            return redirect('admin/products')->with('success', 'Product Updated');
        }
    }

    public function activateAll()
    {

        Product::query()->withoutGlobalScope(ActiveScope::class)
            ->withoutGlobalScope(SoldScope::class)->update(['active' => 1]);
        return back()->with('success', 'All Products Activated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Product Deleted');
    }
}
