<?php

namespace App\Http\Controllers\Company\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompanyManager;
use App\Traits\HasProfile;

class ManagerProfileController extends Controller
{
    use HasProfile;
    /////////////////////////////////////////////////////////////
    public function __construct()
    {
      $this->guard = 'web-manager';
    }
    //////////////////////////////////////////////////////////////////////
    public function get(Request $request)
    {
      return $this->getProfile($request,$this->guard );
    }
    ////////////////////////////////////////////////////////////////////////
    public function update(Request $request)
    {
      return $this->updateProfile( $request, CompanyManager::class ,$this->guard );
    }
}
