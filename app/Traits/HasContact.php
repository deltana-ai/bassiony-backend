<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait HasContact
{

  ////////////////////////////////////////////////////////////////////////////////
  public function contact( $request, $modelClass )
  {
      $user = auth()->user();

      if (!$user) {
          return response()->json(['message' => 'Unauthenticated'], 401);
      }
      $data = $request->validate([
          'name' => ['required', 'string', 'max:255'],
          'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
          'message' => ['required', 'string', 'max:255'],
      ]);
      $model = $modelClass::create($data);

      return response()->json([
        'message'=>'profile information updated successfully',

      ]);
  }

}
