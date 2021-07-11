<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\Media;
use App\Models\User;
use App\Traits\InteractWithUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use InteractWithUpload;

    public function detail()
    {
        return \Auth::user();
    }

    /**
     * @param Request $request
     * @throws \Throwable
     */
    public function upload(Request $request)
    {
        DB::transaction(function () use ($request) {
            $uploadData = $this->uploadImage($request, Auth::user());
            /// todo handle it in Interact with upload making a method which remove the old one and create new
            $user = \Auth::user();

            $hasPreviousImage = Auth::user()->getRawOriginal('profile_url');

            if (!empty($hasPreviousImage)) {
                $previous_media = Auth::user()->media()->where('type', User::MEDIA_UPLOAD)->first();
            
                Storage::delete('public/' . $hasPreviousImage);
                $previous_media->delete();
            }

            $user->fill(['profile_url' => $uploadData['absolute_path']]);
            $user->save();
        });

    }
}
