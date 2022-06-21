<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Stripe\StripeClient;

class StripeController extends Controller
{
    /**
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function generate(Product $product)
    {
        $stripe = new StripeClient(env('STRIPE_SK'));
        $paymentIntent = $stripe->paymentIntents->create([
            'amount' => $product->price * 100,
            'currency' => 'usd',
            'capture_method' => 'manual'
        ]);

        return [
            'client_secret' => $paymentIntent->client_secret
        ];
    }
}
