<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HasProfile;
use App\Models\User;

class ClientProfileController extends Controller
{
    use HasProfile;

    public function get(Request $request)
    {
        dd(auth()->user());
      return $this->getProfile($request);
    }

    public function update(Request $request)
    {
      return $this->updateProfile( $request, User::class );
    }
}
