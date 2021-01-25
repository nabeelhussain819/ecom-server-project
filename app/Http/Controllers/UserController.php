<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        //
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
    }
    //this will show products associated with the user
    public function showUserProducts(User $user, Request $request)
    {
       $userProducts = User::with('products')->where('id',$user->id)->first();
       return view('customer.user-products',['customer' =>  User::where('id', $user->id)->first(), 'customerProduct' => $userProducts->products]);
    }

    public function showUserServices(User $user, Request $request)
    {
        $userServices = User::with('services')->where('id',$user->id)->first();
        return view('customer.user-services',['customer' =>  User::where('id', $user->id)->first(), 'userServices' => $userServices->services]);
    }
    public function activateAllProducts(Request $request,User $user)
    {
        Product::where('user_id',$user->id)->update(['active' => $request->get('checkbox')]);
        return back()->with('success', "All Products Activated Of this user {$user->name}");
    }
    public function activateAllServices(Request $request,User $user)
    {
        Service::where('user_id',$user->id)->update(['active' => $request->get('checkbox')]);
        return back()->with('success', "All Services Activated Of this user {$user->name}");
    }

}
