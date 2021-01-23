<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Model\Category;
use App\Model\Product;
use App\Model\ProductsCategory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('products.index',[
            'products'=> ProductsCategory::with('product')->whereHas('product',function ($query){
                $query->where('active',true);
            })->orderBy('created_at','ASC')->paginate(10)
        ]);
    }

    public function inActive()
    {
        $inActiveProduct= ProductsCategory::with('product')->whereHas('product',function ($query){
            $query->where('active',false);
        })->orderBy('created_at','ASC')->paginate(10);
        return view('products.in-active',['products' => $inActiveProduct]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('products.create',['category' => Category::where('active',1)->get()]);
    }

    public function search(Request $request)
    {
        $search = $request->get('search');
        $products = ProductsCategory::with('product')->whereHas('product', function ($query) use ($search){
            $query->where('active',true)->where('name','like','%' . $search . '%');
        })->paginate(10);
        return view('products.index',['products' => $products]);
    }
    public function searchInActive(Request $request)
    {
        $search = $request->get('search');
        $products = ProductsCategory::with('product')->whereHas('product', function ($query) use ($search){
            $query->where('active',false)->where('name','like','%' . $search . '%');
        })->paginate(10);
        return view('products.in-active',['products' => $products]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        $productCat = new ProductsCategory();
        $product = new Product();
        $product->guid = \Illuminate\Support\Str::uuid();
        $request['user_id'] = auth()->user()->getAuthIdentifier();
        $product->fill($request->all())->save();
        $productCat->product_id = $product->id;
        $productCat->category_id = $request->category_id;
        $productCat->save();
        return redirect('admin/products')->with('success','Product Added');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('products.edit',[
            'product'=>Product::with('productsCategories')->findOrFail($id),
            'category' => Category::where('active',true)->get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, $id)
    {
        $product = Product::find($id);
        $product->fill($request->all())->update();
        ProductsCategory::where('product_id',$product->id)->update(['category_id' => $request->category_id]);
        return redirect('admin/products')->with('success','Product Updated');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return back()->with('success','Product Deleted');
    }
}
