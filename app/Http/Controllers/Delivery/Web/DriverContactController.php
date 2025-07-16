<?php

namespace App\Http\Controllers\Delivery\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HasContact;
use App\Models\Driver;
class DriverContactController extends Controller
{
    use HasContact;
    public function __construct()
    {
      $this->guard = 'driver';
    }
    public function store(Request $request)
    {
      return $this->contact( $request, Driver::class ,$this->guard );
    }
}
