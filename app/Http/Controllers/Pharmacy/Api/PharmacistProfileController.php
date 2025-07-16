<?php

namespace App\Http\Controllers\Pharmacy\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pharmacist;
use App\Traits\HasProfile;

class PharmacistProfileController extends Controller
{
    use HasProfile;

    public function __construct()
    {
      $this->guard = 'pharmacist';

    }


    public function get(Request $request)
    {
      return $this->getProfile($request ,$this->guard);
    }

    public function update(Request $request)
    {
      return $this->updateProfile( $request, Pharmacist::class ,$this->guard);
    }
}
