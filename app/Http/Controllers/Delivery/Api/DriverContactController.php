<?php

namespace App\Http\Controllers\Delivery\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HasContact;
use App\Models\Driver;
class DriverContactController extends Controller
{
    use HasContact;

    public function store(Request $request)
    {
      return $this->contact( $request, Driver::class ,"driver");
    }
}
