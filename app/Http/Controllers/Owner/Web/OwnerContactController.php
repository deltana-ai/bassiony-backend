<?php

namespace App\Http\Controllers\Owner\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HasContact;
use App\Models\Owner;
class OwnerContactController extends Controller
{
    use HasContact;
    public function __construct()
    {
      $this->guard = 'web-owner';
    }
    public function store(Request $request)
    {
      return $this->contact( $request, Owner::class ,$this->guard);
    }
}
