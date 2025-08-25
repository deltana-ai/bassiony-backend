<?php

namespace App\Repositories;

use App\Interfaces\PharmacistRepositoryInterface;
use App\Models\Pharmacist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class PharmacistRepository extends CrudRepository implements PharmacistRepositoryInterface
{
    protected Model $model;

    public function __construct(Pharmacist $model)
    {
        $this->model = $model;
    }


    public function createPharmacist(array $data): Pharmacist
    {
        $existingUser = Pharmacist::withTrashed()->where('email', $data['email'])->first();

        if ($existingUser && !$existingUser->password && !$existingUser->phone_verified_at) {
            $existingUser->update([
                'name'     => $data['name'],
                'phone'    => $data['phone'],
                'password' => Hash::make($data['password']),
            ]);
            return $existingUser;
        }

        if ($existingUser) {
            throw new \Exception('Pharmacist with this email is already registered.');
        }

        return Pharmacist::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
