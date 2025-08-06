<?php

namespace App\Traits;

use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Helpers\JsonResponse;
use Exception;
use App\Traits\HasUpload;
use HasUpload;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\{Rule,Rules};
use App\Http\Resources\Common\AuthResource;
trait HasProfile
{

  protected $guard = 'web';

  public function getProfile( $request,$guard)
  {
      $user = auth($guard)->user();
      if (!$user) {

        return JsonResponse::respondError('Unauthenticated',401);
      }
      try {
          return JsonResponse::respondSuccess('Profile Information', new AuthResource($user));
      } catch (Exception $e) {
          return JsonResponse::respondError($e->getMessage());
      }


  }

  ////////////////////////////////////////////////////////////////////////////////
  public function updateProfile( $request, $modelClass ,$guard)
  {
    $user = auth($guard)->user();

      if (!$user) {
          return JsonResponse::respondError('Unauthenticated',401);

      }
      $request->validate([
          'name' => ['sometimes', 'string', 'max:255'],
          'email' => ['sometimes', 'string', 'lowercase', 'email', 'max:255', Rule::unique((new $modelClass)->getTable())->ignore($user->id)],
          'phone' => ['sometimes','regex:/^\+\d{1,3}\d{4,14}$/', Rule::unique((new $modelClass)->getTable())->ignore($user->id)],
          'password' => ['sometimes', 'confirmed', Rules\Password::defaults()],
          'address' => ['sometimes', 'string', 'max:255'],
      ]);

      try {
          $data = $request->only(['name','email','phone','address']);
          if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
          }
          $user->update($data);
          return JsonResponse::respondSuccess('profile information updated successfully', new AuthResource($user));
      } catch (Exception $e) {
          return JsonResponse::respondError($e->getMessage());
      }

  }
  //////////////////////////////////////////////////////////////////////////////////////
 public function getUserAddresses( $request,$guard)
 {
     $user = auth($guard)->user();
     if (!$user) {

       return JsonResponse::respondError(__('lang.Unauthenticated'),401);
     }
     try {
       $addresses = UserAddress::where('user_id',$user->id)->get();
       $collection =  UserAddressResource::collection($addresses);
         return JsonResponse::respondSuccess(__('lang.address get successfully'),$collection);
     } catch (Exception $e) {
         return JsonResponse::respondError($e->getMessage());
     }

 }
 ///////////////////////////////////////////////////////////////////////////////////////////////////
 public function updateUserAddress($request , $modelClass ,$guard ,$id){

     $address = UserAddress::find($id);
     if (!$address) {
         return JsonResponse::respondError(__('lang.this address not Found'),404);

     }
     $request->validate([
         'name' => ['sometimes', 'string', 'max:255'],
         'phone' => ['nullable','regex:/^\+\d{1,3}\d{4,14}$/', Rule::unique((new $modelClass)->getTable())->ignore($address->id)],
         'building' => ['sometimes', 'string', 'max:255'],
         'area' => ['sometimes', 'string', 'max:255'],
         'city' => ['sometimes', 'string', 'max:255'],
         'is_default'     => 'sometimes|in:0,1',

     ]);
     try {
       $address->fill($request->only([
           'name', 'phone', 'building', 'area', 'city', 'is_default',
       ]))->save();
         return JsonResponse::respondSuccess(__('lang.profile address updated successfully'), new UserAddressResource($address));
     } catch (Exception $e) {
         return JsonResponse::respondError($e->getMessage());
     }
   }
////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////
     public function storeUserAddress($request , $modelClass ,$guard ){


         $user = auth($guard)->user();

          if (!$user) {
              return JsonResponse::respondError(__('lang.Unauthenticated'),401);
          }
         $request->validate([
             'name' => ['required', 'string', 'max:255'],
             'phone' => ['sometimes','regex:/^\+\d{1,3}\d{4,14}$/', Rule::unique((new $modelClass)->getTable())],
             'building' => ['required', 'string', 'max:255'],
             'area' => ['required', 'string', 'max:255'],
             'city' => ['required', 'string', 'max:255'],
             'is_default'     => 'required|in:0,1',

         ]);
         try {
             $address = new UserAddress;
             $address->user_id = $user->id;
             $address ->name = $request->name ;
             $address ->building = $request->building ;
             $address ->phone = $request->phone ;
             $address ->area = $request->area ;
             $address ->city = $request->city ;
             $address ->is_default = $request->is_default ;
             $address->save();
             return JsonResponse::respondSuccess(__('lang.profile address added successfully'), new UserAddressResource($address));

         } catch (Exception $e) {
             return JsonResponse::respondError($e->getMessage());
         }

   }
   ///////////////////////////////////////////////////////////////////
   public function deleteUserAddress($request , $modelClass ,$guard ,$id){
     $address = UserAddress::find($id);
     if (!$address) {
         return JsonResponse::respondError(__('lang.this address not Found'),404);

     }
     try{
         $address->delete();
         return JsonResponse::respondSuccess(__('lang.profile address deleted successfully'), new UserAddressResource($address));

     } catch (Exception $e) {
         return JsonResponse::respondError($e->getMessage());
     }
   }
   ///////////////////////////////////////////////////////////////////////////////////////////
    public function updateLanguege($request , $modelClass ,$guard ){
      $user = auth($guard)->user();

        if (!$user) {
            return JsonResponse::respondError(__('lang.Unauthenticated'),401);

        }
        $request->validate([
            'language' => ['sometimes', 'in:ar,en'],

        ]);
      try{
        $user->fill($request->only([
            'language'
        ]))->save();

          return JsonResponse::respondSuccess(__('lang.your language updated successfully'));

      } catch (Exception $e) {
          return JsonResponse::respondError($e->getMessage());
      }
    }
    /////////////////////////////////////////////////////////////////////////////
    public function updateImageProfile($request , $modelClass ,$guard ){
      $user = auth($guard)->user();

        if (!$user) {
            return JsonResponse::respondError(__('lang.Unauthenticated'),401);

        }
        $request->validate([
          'image' => 'required|string',

        ]);
      try{
        if ($request->image) {
           if ($user->profile_image && \Storage::disk('public')->exists($user->profile_image)) {
               \Storage::disk('public')->delete($user->profile_image);
           }

           //////////////////////////////////
           $path = $this->storeBase64($request->image,'profiles');
           $user->profile_image = $path;
           $user->save();
           return JsonResponse::respondSuccess(__('lang.profile image updated successfully'));

         }

        return JsonResponse::respondError(__('lang.image not in base64'));

      } catch (Exception $e) {
          return JsonResponse::respondError($e->getMessage());
      }
    }
}
