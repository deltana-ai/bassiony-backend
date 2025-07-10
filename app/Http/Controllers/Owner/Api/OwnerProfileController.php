<?php

namespace App\Http\Controllers\Owner\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Owner;
use App\Traits\HasProfile;

class OwnerProfileController extends Controller
{
    use HasProfile;

    public function get(Request $request)
    {
      return $this->getProfile($request);
    }

    public function update(Request $request)
    {
      return $this->updateProfile( $request, Owner::class );
    }
}
