@component('mail::message')
    <div style="background-color: #f9d9eb; padding:20px;">
        <p>Hello {{$user->name}}</p>
        <p>A request has been made to reset your password. if you made this request please enter verify this code.</p>
        @component('mail::button', [ 'url' => $verificationUrl , 'color' => 'blue', 'target' => '_blank']) 
        <button style="background-color: #ec2a8b; border:0px; border-radius:4px; color:white;padding:12px;font-size:22px; padding-left:60px;padding-right:60px;">{{$verificationUrl}}</button> 
        @endcomponent
        <p> Please remember your email address is your username.</p>
    </div>
@endcomponent