<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\GuidHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Facebook\Facebook;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\JWTAuth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;


    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = RouteServiceProvider::HOME;
    protected $redirectTo = '';
    protected $auth;

    /**
     * LoginController constructor.
     * @param JWTAuth $auth
     */
    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
//        $this->middleware('guest')->except('logout');
    }

    /**
     * Handle a login request to the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @todo right now simple JWT TOKEN after move to passport soon
     */
    public function login(Request $request)
    {

        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->genericResponse(false, 'You attempt number of time your account has been blocked',
                null, ['errors' => [
                    "you've been locked"
                ]]);
        }

        if (!Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            throw ValidationException::withMessages([
                $this->username() => [trans('auth.failed')],
            ]);
        }


        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);
        $user = Auth::user();
        $user->validateEmailVerification();

        $tokenResult = $user->createToken('Personal Access Token')->accessToken;

        return $this->genericResponse(true, 'Successful login',
            200, ['data' => $request->user(),
                'token' => $tokenResult
            ]);
    }

    private function unProcessEntityResponse($message = '')
    {
        return $this->genericResponse(false, $message,
            422, ['errors' => [
                'email' => 'Invalid address or password',
            ]]);
    }

    public function facebookLogin(Request $request)
    {
        $fb = new Facebook([
            'app_id' => config('app.facebook.app_id'),
            'app_secret' => config('app.facebook.app_secret'),
            'default_graph_version' => 'v8.0',
        ]);

        $response = $fb->get('/me?fields=name,email', $request->get('accessToken'));

        $fbUser = $response->getGraphUser();
        $internalUser = User::where('email', $fbUser->getEmail())->first();
        if ($internalUser === null) {
            $internalUser = new User([
                'name' => $fbUser->getName(),
                'email' => $fbUser->getEmail(),
                'password' => Hash::make(Str::random(8)),
                'guid' => GuidHelper::getGuid(),
                'email_verified_at' => Carbon::now()
            ]);
            $internalUser->save();
        }
        Auth::login($internalUser);

        return $this->genericResponse(true, 'Successful login', 200, [
            'data' => $request->user(),
            'token' => $internalUser->createToken('Personal Access Token')->accessToken
        ]);
    }

    public function googleLogin(Request $request)
    {
        $client = new \Google_Client(['client_id' => config('app.google.client_id')]);
        $valid = $client->verifyIdToken($request->get('id_token'));

        if ($valid) {
            $googleUser = $request->get('user');
            $internalUser = User::where('email', $googleUser['email'])->first();
            if ($internalUser === null) {
                $internalUser = new User(array_merge(
                    $googleUser,
                    [
                        'password' => Hash::make(Str::random(8)),
                        'guid' => GuidHelper::getGuid(),
                        'email_verified_at' => Carbon::now()
                    ]
                ));
                $internalUser->save();
            }

            Auth::login($internalUser);

            $user = auth()->check() ? auth()->user() : $request->user();

            return $this->genericResponse(true, 'Successful login', 200, [
                'data' => $user,
                'token' => $internalUser->createToken('Personal Access Token')->accessToken
            ]);
        }

        throw ValidationException::withMessages(['token' => 'Invalid token provided.']);
    }
}
