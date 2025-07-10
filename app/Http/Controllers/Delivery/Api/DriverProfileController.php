<?php

namespace App\Http\Controllers\Delivery\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Driver;
use App\Traits\HasProfile;

class DriverProfileController extends Controller
{
    use HasProfile;

    public function get(Request $request)
    {
      return $this->getProfile($request);
    }

    public function update(Request $request)
    {
      return $this->updateProfile( $request, Driver::class );
    }
}
