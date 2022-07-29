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
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function check(Request $request){
        $user = User::where('email', '=', $request->email)->first();       
        if ($user) {
            Otp::where('email','=',$user->email)->delete();
            Notification::send($user, new ForgetPasswordVerification());
         }else{
            throw ValidationException::withMessages(['email' => trans($user)]);
         }
    }
    public function verifyOtp(Request $request){

        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            throw ValidationException::withMessages(['message' => "add All Feilds"]);
        }
        if (Otp::where('otp', $request->otp)->where('email', $request->email)->count() != 1) {
            throw ValidationException::withMessages(['message' => "Otp Is Incorrect"]);
        } 
        return $this->genericResponse(200, 'You can change your password.');
     
    }
    protected function validator(array $data)
    {
        return  Validator::make($data,[
            'otp' => 'required',
            'email' => 'required|email'
        ]);
    }
}
