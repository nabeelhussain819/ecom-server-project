<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class StripeController extends Controller
{
    protected $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(env('STRIPE_SK'));
    }

    /**
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function generate(Product $product)
    {
        $paymentIntent = $this->stripe->paymentIntents->create([
            'amount' => $product->getPrice() * 100,
            'currency' => 'usd',
            'capture_method' => 'manual',
            'transfer_data' => [
                'destination' => $product->user->stripe_account_id,
            ],
        ]);

        return ['client_secret' => $paymentIntent->client_secret];
    }

    /**
     * @param Request $request
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function feature(Request $request)
    {
        $paymentIntent = $this->stripe->paymentIntents->create([
            'amount' => Product::getFeaturedPrice($request->get('choice')) * 100,
            'currency' => 'usd'
        ]);

        return ['client_secret' => $paymentIntent->client_secret];
    }

    public function hire()
    {

    }
}
