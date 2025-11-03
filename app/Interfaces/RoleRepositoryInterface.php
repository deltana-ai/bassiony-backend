<?php

namespace App\Interfaces;

use App\Interfaces\Interfaces\ICrudRepository;

interface RoleRepositoryInterface extends ICrudRepository
{

    public function createRole(array $data);

    public function updateRole($role, array $data);

    public function getPermissions();

    public function deleteRoles( array $roleIds ,$user_model);
}
