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


    protected $stripe;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->stripe = new StripeClient(env('STRIPE_SK'));
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Order::whereDate('created_at', '<=', Carbon::now()->subDays(2)->toDateTimeString())
            ->where('status', Order::STATUS_UNCAPTURED)
            ->whereNotNull('payment_intent')
            ->each(function (Order $order) {
                if ($order->status !== Order::STATUS_REFUNDED) {
                    $this->stripe->paymentIntents->capture($order->payment_intent);
                    $order->status = Order::STATUS_PAID;
                    $order->update();
                }
            });
    }
}
