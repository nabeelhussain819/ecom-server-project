<?php

namespace App\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class GuidHelper extends Arr
{
    public static function getGuid(): string

    {
        return Str::uuid()->toString();
    }
}