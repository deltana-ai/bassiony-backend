<?php

namespace App\Http\Controllers\Pharmacy\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HasContact;
use App\Models\Pharmacist;
class PharmacistContactController extends Controller
{
    use HasContact;

    public function __construct()
    {
      $this->guard = 'pharmacist';

    }


    public function store(Request $request)
    {
      return $this->contact( $request, Pharmacist::class ,$this->guard );
    }
}
