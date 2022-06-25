<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ArrayHelper;
use App\Helpers\GuidHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Models\User;
use App\Notifications\OnboardingRequired;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Stripe\StripeClient;
use Tymon\JWTAuth\JWTAuth;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '';
    protected $auth;
    protected $stripe;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
        $this->stripe = new StripeClient(env('STRIPE_SK'));
        // $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'guid' => $data['guid'],
        ]);

        $accountLink = $this->stripe->accountLinks->create([
            'account' => $user->stripe_account_id,
            'refresh_url' => 'http://localhost:3000', // @TODO: replace with env variable
            'return_url' => 'http://localhost:3000', // @TODO: replace with env variable
            'type' => 'account_onboarding'
        ]);

        $user->notify(new OnboardingRequired($accountLink));

        return $user;
    }

    /**
     * @param Request $request
     * @throws \Throwable
     */
    public function register(RegistrationRequest $request)
    {

        return DB::transaction(function () use ($request) {
            $validator = $this->validator($request->all());
            if (!$validator->fails()) {
                // dd(ArrayHelper::merge($request->all(),['guid'=>GuidHelper::getGuid()]));

                event(new Registered($user = $this->create(ArrayHelper::merge($request->all(), ['guid' => GuidHelper::getGuid()]))));
//            $user = Auth::user();
//            $token = $user->createToken('Personal Access Token')->accessToken;
                return response()->json([
                    'success' => true,
//                'data' => $user,
                    'message' => "Please verify your email"
                ], 200);
            }
            return response()->json([
                'success' => false,
                'errors' => $this,
                'message' => $validator->getMessageBag()
            ], 401);
        });
    }
}
