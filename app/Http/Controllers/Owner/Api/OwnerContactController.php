<?php

namespace App\Http\Controllers\Owner\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HasContact;
use App\Models\Owner;
class OwnerContactController extends Controller
{
    use HasContact;

    public function store(Request $request)
    {
      return $this->contact( $request, Owner::class );
    }
}
