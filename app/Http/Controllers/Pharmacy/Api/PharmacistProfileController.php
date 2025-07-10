<?php

namespace App\Http\Controllers\Pharmacy\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pharmacist;

class PharmacistProfileController extends Controller
{
    use HasProfile;

    public function get(Request $request)
    {
      return $this->getProfile($request);
    }

    public function update(Request $request)
    {
      return $this->updateProfile( $request, Pharmacist::class );
    }
}
