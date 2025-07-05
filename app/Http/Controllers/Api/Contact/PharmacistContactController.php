<?php

namespace App\Http\Controllers\Api\Contact;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HasContact;
use App\Models\Pharmacist;
class PharmacistContactController extends Controller
{
    use HasContact;

    public function store(Request $request)
    {
      return $this->contact( $request, Pharmacist::class );
    }
}
