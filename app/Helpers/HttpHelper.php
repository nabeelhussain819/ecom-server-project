<?php

namespace App\Helpers;

class HttpHelper
{
    public static function request($url, $body, $method = 'GET', $headers = [])
    {
        $ch = curl_init($url);

        if ($method === 'GET') {
            // @TODO: implement as required
        } else {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
