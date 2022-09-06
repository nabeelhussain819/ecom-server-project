<?php

namespace App\Notifications;

use App\Mail\BaseMailable;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlaced extends Notification
{
    use Queueable;

    protected  $order;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return
     */
    public function toMail($notifiable)
    {
        $baseMailable = new BaseMailable();
        $product = Product::where('id', $this->order->product_id)->with('user')->first();
        $shipping = ShippingDetail::where('id', $this->order->shipping_detail_id)->get();
        return $baseMailable->to($notifiable->email)
            ->subject($notifiable->name . '- Order Placed')
            ->markdown('emails.order.placed', ['user' => $notifiable, 'order' => $this->order, 'product' => $product, 'shipping' => $shipping]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
