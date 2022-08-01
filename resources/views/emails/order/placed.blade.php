@component('mail::message')
    <div>
        <p>Dear {{$user->name}}</p>
        <p>Your order # {{$order->id}} has been placed</p>
    </div>
@endcomponent
