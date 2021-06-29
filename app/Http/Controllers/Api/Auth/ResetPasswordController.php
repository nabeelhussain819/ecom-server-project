<?php


namespace App\Http\Controllers\Api\Auth;


use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected function sendResetResponse(Request $request, $response): array
    {
        return ['message' => trans($response)];
    }

    protected function sendResetFailedResponse(Request $request, $response)
    {
        throw ValidationException::withMessages(['email' => trans($response)]);
    }
}
