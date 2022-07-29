<?php


namespace App\Http\Controllers\Api\Auth;
use App\Models\User;
use App\Models\Otp;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ForgetPasswordVerification;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;
    public function Check(Request $request){
        $user = User::where('email', '=', $request->email)->first();       
        $verificationCode = mt_rand(1000,9999);
        $otp = new Otp();
        $otp->otp = $verificationCode;
        $otp->user_id = $user->id;
        $otp->save();
        Notification::send($request->email,$verificationCode, new ForgetPasswordVerification());
        if ($user) {
            $verificationCode = mt_rand(1000,9999);
            $otp = new Otp();
            $otp->otp = $verificationCode;
            $otp->user_id = $user->id;
            $otp->save();
            Notification::send($request->email,$verificationCode, new ForgetPasswordVerification());
         }else{
            return $this->genericResponse(false,'Invalid Email',
            422, ['errors' => [
                'email' => 'Invalid Email Address',
            ]]);
         }
     
    }
    public function VerifyOtp(Request $request){
        $user = User::where('email', '=', $request->email)->first();       
       
        if ($user) {
         }else{
            return $this->genericResponse(false,'Invalid Email',
            422, ['errors' => [
                'email' => 'Invalid Email Address',
            ]]);
         }
     
    }
    protected function sendResetLinkResponse(Request $request, $response): array
    {
       
        return ['message' => trans($response)];
    }

    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        throw ValidationException::withMessages(['email' => trans($response)]);
    }
}
