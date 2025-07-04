<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\{ContactFrom,ContactTo};

trait HasContact
{

  ////////////////////////////////////////////////////////////////////////////////
  public function contact( $request, $modelClass )
  {
      $user = auth()->user();

      if (!$user) {
          return response()->json(['message' => 'Unauthenticated'], 401);
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
      Mail::to("zeinabyounes099@gmail.com")->send(new ContactTo($contact));

      return response()->json([
        'message'=>'contact information sent successfully',
         'data' =>$contact,
      ]);
  }

}
