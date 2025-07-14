<?php

namespace App\Traits;

use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\{Rule,Rules};
use App\Traits\HttpResponses;
use App\Helpers\JsonResponse;
trait HasProfile
{
  use HttpResponses;

  public function getProfile( $request)
  {
      $user = auth()->user();

      if (!$user) {
        return $this->error(null, "Unauthenticated",  401);
      }

      //return JsonResponse::respondSuccess("profile information", $user,  200);
       return $this->success($user, "profile information",  200);


  }

  ////////////////////////////////////////////////////////////////////////////////
  public function updateProfile( $request, $modelClass )
  {
      $user = auth()->user();

      if (!$user) {
        return $this->error(null, "Unauthenticated",  401);
      }
      
      $request->validate([
          'name' => ['sometimes', 'string', 'max:255'],
          'email' => ['sometimes', 'string', 'lowercase', 'email', 'max:255', Rule::unique((new $modelClass)->getTable())->ignore($user->id)],
          'phone' => ['sometimes', 'regex:/^[0-9]{10,15}$/', Rule::unique((new $modelClass)->getTable())->ignore($user->id)],
          'password' => ['sometimes', 'confirmed', Rules\Password::defaults()],
          'address' => ['sometimes', 'string', 'max:255'],
       
      ]);
      $data = $request->only(['name','email','phone','address']);
      if ($request->filled('password')) {
        $data['password'] = Hash::make($request->password);
      }
      $user->update($data);
      return $this->success($user, "profile information updated successfully",  200);
  }

}
