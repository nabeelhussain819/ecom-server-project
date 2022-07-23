<?php

namespace App\Console\Commands;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Stripe\StripeClient;

class CaptureFunds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'capture:funds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Captures funds for orders that are past the 2 days protection.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $stripe = new StripeClient(env('STRIPE_SK'));
        Order::whereDate('created_at', '<=', Carbon::now()->subDays(2)->toDateTimeString())
            ->where('status', Order::STATUS_UNCAPTURED)
            ->whereNotNull('payment_intent')
            ->each(function (Order $order) use ($stripe) {
                $stripe->paymentIntents->capture($order->payment_intent);
                $order->status = Order::STATUS_PAID;
                $order->update();
            });
    }
}
