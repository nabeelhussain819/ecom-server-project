<?php

namespace App\Observers;

use App\Helpers\StringHelper;
use App\Models\User;

//use Stripe\StripeClient;

class UserObserver
{
    /**
     * @param User $user
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function creating(User $user)
    {
//        $stripe = new StripeClient(env('STRIPE_SK'));
//        $account = $stripe->accounts->create(['type' => 'express']);

        $user->stripe_account_id = StringHelper::random(4);
    }
}
