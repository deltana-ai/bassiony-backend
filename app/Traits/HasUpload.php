<?php

namespace App\Traits;
// use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Helpers\JsonResponse;
trait HasUpload
{
  public function storeImage($photo)
  {
    $image = $photo;
    $realMime = $image->mime();
    $originname = $photo->getClientOriginalName();
    $nameAbsolute = pathinfo($originname ,PATHINFO_FILENAME);
    $extension = explode('/',$realMime)[1];
    $filename = time().'-' .$nameAbsolute.'.'.$extension ;
    $path = "uploads/images/{$filename}";
    Storage::disk('public')->put($path ,(string) $image );
    return $path;
  }
  /*
  *
  */
  public function storeBase64($base64 ,$folder)
  {
    if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches))
    {
      $extension = $matches[1];
      $base64 = substr($base64 , strpos($base64, ',')+1);
      $image = base64_decode($base64);
      $filename = time() . '-' . Str::random(10) . '.' . $extension;
      $path = "uploads/{$folder}/{$filename}";
      Storage::disk('public')->put($path ,(string) $image );
      return $path;
    }else{
      return null;

    }

  }
  public function deleteFile($path)
  {
    if (Storage::disk('public')->exists($path))
    {
      Storage::disk('public')->delete($path);
    }
  }
}
