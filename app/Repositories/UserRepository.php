<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserRepository extends CrudRepository implements UserRepositoryInterface
{
    protected Model $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function createUser(array $data): User
    {
        $existingUser = User::withTrashed()->where('email', $data['email'])->first();

        if ($existingUser && !$existingUser->password && !$existingUser->phone_verified_at) {
            $existingUser->update([
                'name'     => $data['name'],
                'phone'    => $data['phone'],
                'password' => Hash::make($data['password']),
            ]);
            return $existingUser;
        }

        if ($existingUser) {
            throw new \Exception('User with this email is already registered.');
        }

        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'],
            'password' => Hash::make($data['password']),
        ]);
    }

}

