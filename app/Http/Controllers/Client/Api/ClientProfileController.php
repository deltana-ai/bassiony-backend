<?php

namespace App\Http\Controllers\Client\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HasProfile;
use App\Models\User;

class ClientProfileController extends Controller
{
    use HasProfile;

    public function get(Request $request)
    {
      return $this->getProfile($request ,"client");
    }

    public function update(Request $request)
    {
      return $this->updateProfile( $request, User::class ,"client");
    }
}
