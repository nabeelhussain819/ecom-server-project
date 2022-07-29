@component('mail::message')
    <div>
        <p>Dear {{$user->name}}</p>
        <p>Please click on the link below to complete your registration.</p>
        @component('mail::button', ['url' => $verificationUrl, 'color' => 'blue', 'target' => '_blank'])
        <a href="{{$verificationUrl}}">Verify Email Address</a>  
        @endcomponent
        <p> Please remember your email address is your username.</p>
        <p>
If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser: <a href="{{$verificationUrl}}">{{$verificationUrl}}</a></p>
    </div>
@endcomponent