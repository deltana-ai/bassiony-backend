<?php

namespace App\Http\Controllers\Owner\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Owner;
use App\Traits\HasProfile;

class OwnerProfileController extends Controller
{
    use HasProfile;

    public function get(Request $request)
    {
      return $this->getProfile($request,"web-owner");
    }

    public function update(Request $request)
    {
      return $this->updateProfile( $request, Owner::class ,"web-owner");
    }
}
