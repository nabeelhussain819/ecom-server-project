<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ArrayHelper;
use App\Helpers\GuidHelper;
use App\Http\Controllers\Controller;
use App\Model\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
        // $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
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
     * @param  array $data
     * @return \App\Model\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'guid' => $data['guid'],
        ]);
    }

    public function register(Request $request)
    {
        $validator = $this->validator($request->all());

        if (!$validator->fails()) {
           // dd(ArrayHelper::merge($request->all(),['guid'=>GuidHelper::getGuid()]));
            $user = $this->create(ArrayHelper::merge($request->all(),['guid'=>GuidHelper::getGuid()]));

            $token = $this->auth->attempt($request->only('email', 'password'));
            return response()->json([
                'success' => true,
                'data' => $user,
                'token' => $token
            ],200);
        }
        return response()->json([
            'success' => false,
            'errors' => $this,
            'token' => $validator->errors(),
        ],401);
    }
}
