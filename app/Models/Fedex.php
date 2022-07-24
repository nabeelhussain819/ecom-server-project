<?php

namespace App\Models;

use App\Helpers\HttpHelper;

class Fedex
{
    const API = 'https://apis-sandbox.fedex.com';

    private static function authorize()
    {
        $url = self::API . '/oauth/token';
        $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
        $body = [
            'grant_type' => 'client_credentials',
            'client_id' => env('FEDEX_API_KEY'),
            'client_secret' => env('FEDEX_SECRET_KEY'),
        ];

        return HttpHelper::request($url, $body, 'POST', $headers);
    }
}
