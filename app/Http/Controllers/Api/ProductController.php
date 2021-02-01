<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductsCategories;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        //
        return ProductsCategories::with('products','category')
            ->whereHas('products', function($query){
            $query->where('active',1);
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
     * @param  \Illuminate\Http\Request  $request
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

        return $this->genericResponse(true, 'Product Created', 200, ['product' => $product->withCategories()]);
    }

    /**
     * @param Product $product
     * @return Product
     */
    public function show(Product $product)
    {
        return $product->withCategories();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
       $product->fill($request->all())->update();
        //ProductsCategories::where('product_id',$product->id)->update(['category_id' => $request->category_id]);
        return $this->genericResponse(true, "$product->name Product Updated" , 200, ['product' => $product->withCategories()]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        Product::destroy($id);
        return response()->json(['message' => 'Product Deleted Successfully'],200);
    }
}
