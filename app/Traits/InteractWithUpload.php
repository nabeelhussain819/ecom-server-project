<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 1/23/2021
 * Time: 3:40 AM
 */

namespace App\Traits;

use App\Helpers\ArrayHelper;
use App\Helpers\GuidHelper;
use App\Models\Media;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait InteractWithUpload
{
    public function uploadImage(Request $request, $entity)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $extension = $file->getClientOriginalExtension();
            $guid = GuidHelper::getGuid();
            $path = User::getUploadPath() . $entity::MEDIA_UPLOAD;
            $name = "{$path}/{$guid}.{$extension}";
            $media = new Media();

            $properties = [
                'name' => $name,
                'extension' => $extension,
                'type' => $entity::MEDIA_UPLOAD,
                'user_id' => \Auth::user()->id,
                'active' => true,
            ];

            $entityProperty = [];

            if ($entity instanceof Product) {
                $entityProperty = ['product_id' => $entity->id];
            }

            $media->fill(ArrayHelper::merge($properties, $entityProperty));

            $media->save();

            Storage::putFileAs(
                'public/' . $path, $request->file('file'), "{$guid}.{$extension}"
            );

            return [
                'uid' => $media->id,
                'name' => $media->url,
                'status' => 'done',
                'url' => $media->url,
                'absolute_path'=>$name
            ];
        }
        throw new NotFoundHttpException('file not attach');
    }
}