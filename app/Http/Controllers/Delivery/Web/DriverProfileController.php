<?php

namespace App\Http\Controllers\Delivery\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Driver;
use App\Traits\HasProfile;

class DriverProfileController extends Controller
{
    use HasProfile;
    public function __construct()
    {
      $this->guard = 'driver';
    }
    public function get(Request $request)
    {
      return $this->getProfile($request,$this->guard);
    }

    public function update(Request $request)
    {
      return $this->updateProfile( $request, Driver::class ,$this->guard);
    }
}
