<?php

namespace App\Http\Controllers\Owner\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Owner;
use App\Traits\HasProfile;

class OwnerProfileController extends Controller
{
    use HasProfile;
    public function __construct()
    {
      $this->guard = 'web-owner';
    }
    public function get(Request $request)
    {
      return $this->getProfile($request,$this->guard);
    }

    public function update(Request $request)
    {
      return $this->updateProfile( $request, Owner::class ,$this->guard);
    }
}
