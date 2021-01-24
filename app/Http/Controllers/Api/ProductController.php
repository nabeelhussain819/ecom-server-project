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
        return ProductsCategories::with('product','category')
            ->whereHas('product', function($query){
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
        //@todo MNN make it proper
        $productCat = new ProductsCategories();
        $product = new Product();
        $request['guid'] = \Illuminate\Support\Str::uuid();
        //temporary 1, for testing
        $request['user_id'] = 1;
        $product->fill($request->all())->save();
        $productCat->product_id = $product->id;
        $productCat->category_id = $request->category_id;
        $productCat->save();
        return response()->json([
                'message' => 'Product added successfully'
            ],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function update(Request $request, $id)
    {
        //
       $product = Product::find($id);
       $product->fill($request->all())->update();
       ProductsCategories::where('product_id',$product->id)->update(['category_id' => $request->category_id]);
       return response()->json(['message'=> 'Product Updated Successfully']);
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
