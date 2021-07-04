<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function detail()
    {
        return \Auth::user();
    }

    public function upload()
    {
        dd('work in progress');
    }
}
