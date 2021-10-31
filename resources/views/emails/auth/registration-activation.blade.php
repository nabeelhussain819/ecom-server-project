@component('mail::message')
    <div>
        <p>Dear {{$user->name}}</p>
        <p>Please click on the link below to complete your registration.</p>
        @component('mail::button', ['url' => $verificationUrl, 'color' => 'blue', 'target' => '_blank'])
            Verify Email Address
        @endcomponent
        <p> Please remember your email address is your username.</p>
    </div>
@endcomponent