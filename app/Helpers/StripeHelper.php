<?php

namespace App\Helpers;

use App\Models\User;
use Stripe\StripeClient;

class StripeHelper
{
    public static function createAccountLink(User $user)
    {
        $stripe = new StripeClient(env('STRIPE_SK'));

        return $stripe->accountLinks->create([
            'account' => $user->stripe_account_id,
            'refresh_url' => env('STRIPE_REFRESH_URL'),
            'return_url' => env('STRIPE_RETURN_URL'),
            'type' => 'account_onboarding'
        ]);
    }
}
