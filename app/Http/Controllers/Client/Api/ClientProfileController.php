<?php

<<<<<<< HEAD:app/Http/Controllers/Api/Profile/ClientProfileController.php
namespace App\Http\Controllers\Api\Profile;
=======
namespace App\Http\Controllers\Client\Api;
>>>>>>> ca9b657 (update structure 1):app/Http/Controllers/Client/Api/ClientProfileController.php

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HasProfile;
use App\Models\User;

class ClientProfileController extends Controller
{
    use HasProfile;

    public function get(Request $request)
    {
      return $this->getProfile($request);
    }

    public function update(Request $request)
    {
      return $this->updateProfile( $request, User::class );
    }
}
