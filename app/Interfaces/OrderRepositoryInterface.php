<?php

namespace App\Interfaces;


interface OrderRepositoryInterface
{

    public function all($with = [], $conditions = [], $columns = array('*'));

    public function createOrder(array $data, $user);


}
