<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\{ContactFrom,ContactTo};
use App\Traits\HttpResponses;
use App\Models\Contact;
trait HasContact
{
  use HttpResponses;

  ////////////////////////////////////////////////////////////////////////////////
  public function contact( $request, $modelClass )
  {
      $user = auth()->user();

      if (!$user) {
          return $this->error(null, "Unauthenticated",  401);
      }
      $data = $request->validate([
          'name' => ['required', 'string', 'max:255'],
          'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
          'message' => ['required', 'string', 'max:255'],
      ]);
      $data['contactable_type'] = $modelClass ;
      $data['contactable_id'] = $user->id;
      $contact = Contact::create($data);
      Mail::to($data['email'])->send(new ContactFrom($contact));
      Mail::to("zeinabagban93@gmail.com")->send(new ContactTo($contact));
      return $this->success($contact, "contact information sent successfully",  200);

  }

}
