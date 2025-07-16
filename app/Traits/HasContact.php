<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

use App\Helpers\JsonResponse;
use App\Models\Contact;
use Illuminate\Support\Facades\Mail;
use App\Mail\{ContactFrom,ContactTo};
trait HasContact
{
  protected $guard = 'web';

  ////////////////////////////////////////////////////////////////////////////////
  public function contact( $request, $modelClass,$guard )
  {
      $user = auth($guard)->user();

      if (!$user) {

        return JsonResponse::respondError('Unauthenticated',401);
      }
      $data = $request->validate([
          'name' => ['required', 'string', 'max:255'],
          'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
          'message' => ['required', 'string', 'max:255'],

      ]);
      try {
          $data['contactable_type'] = $modelClass ;
          $data['contactable_id'] = $user->id;
          $contact = Contact::create($data);
          Mail::to($data['email'])->send(new ContactFrom($contact));
          Mail::to(config('mail.admin.address'))->send(new ContactTo($contact));
          return JsonResponse::respondSuccess('Your Contact sent Successfully');
      } catch (Exception $e) {
          return JsonResponse::respondError($e->getMessage());
      }

  }

}
