<?php

namespace App\Http\Controllers\Client\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HasContact;
use App\Models\User;
class ClientContactController extends Controller
{
    use HasContact;
    public function __construct()
    {
      $this->guard = 'client';

    }

    public function store(Request $request)
    {
      return $this->contact( $request, User::class ,$this->guard);
    }

}
