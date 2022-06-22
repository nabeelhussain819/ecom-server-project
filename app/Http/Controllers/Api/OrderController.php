<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;

class OrderController extends Controller
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
    public function create(Request $request)
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
        return DB::transaction(function () use ($request) {
            $order = new Order();
            $shipping = new ShippingDetail();
            $shipping->fill($request->get("shippingDetail"));
            $shipping->user_id = Auth::user()->id;
            $shipping->save();


            $product = Product::getByGuid($request->get('product_id'));
            $order->product_id = $product->id;
            $order->price = $product->price;
            $order->shipping_detail_id = $shipping->id;
            $order->status = Order::STATUS_UNPAID;
            $order->save();

            return $order;
        });
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function update(Order $order, Request $request)
    {
        $shouldUpdate = true;
        if ($request->has('status')) {
            $stripe = new StripeClient(env('STRIPE_SK'));
            $paymentIntent = $stripe->paymentIntents->retrieve($request->get('payment_intent'));

            if ($paymentIntent->id !== $request->get('payment_intent'))
                $shouldUpdate = false;
        }

        if ($shouldUpdate) {
            $order->fill($request->all());
            $order->update();
        }

        return $order;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
