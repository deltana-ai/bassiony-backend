<?php

namespace App\Http\Controllers\Company\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HasContact;
use App\Models\CompanyManager;
class ManagerContactController extends Controller
{
    use HasContact;
    ////////////////////////////////////////////////////////////
    public function __construct()
    {
      $this->guard = 'web-manager';
    }
    //////////////////////////////////////////////////////////////
    public function store(Request $request)
    {
      return $this->contact( $request, CompanyManager::class ,$this->guard  );
    }
}
