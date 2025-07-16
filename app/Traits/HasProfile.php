<?php

namespace App\Traits;

use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Helpers\JsonResponse;
use Exception;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\{Rule,Rules};
use App\Http\Resources\Common\AuthResource;
trait HasProfile
{

  protected $guard = 'web';

  public function getProfile( $request,$guard)
  {
      $user = auth($guard)->user();
      if (!$user) {

        return JsonResponse::respondError('Unauthenticated',401);
      }
      try {
          return JsonResponse::respondSuccess('Profile Information', new AuthResource($user));
      } catch (Exception $e) {
          return JsonResponse::respondError($e->getMessage());
      }


  }

  ////////////////////////////////////////////////////////////////////////////////
  public function updateProfile( $request, $modelClass ,$guard)
  {
    $user = auth($guard)->user();

      if (!$user) {
          return JsonResponse::respondError('Unauthenticated',401);

      }
      $request->validate([
          'name' => ['sometimes', 'string', 'max:255'],
          'email' => ['sometimes', 'string', 'lowercase', 'email', 'max:255', Rule::unique((new $modelClass)->getTable())->ignore($user->id)],
          'phone' => ['sometimes','regex:/^\+\d{1,3}\d{4,14}$/', Rule::unique((new $modelClass)->getTable())->ignore($user->id)],
          'password' => ['sometimes', 'confirmed', Rules\Password::defaults()],
          'address' => ['sometimes', 'string', 'max:255'],
      ]);

      try {
          $data = $request->only(['name','email','phone','address']);
          if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
          }
          $user->update($data);
          return JsonResponse::respondSuccess('profile information updated successfully', new AuthResource($user));
      } catch (Exception $e) {
          return JsonResponse::respondError($e->getMessage());
      }

  }

}
