<?php

namespace App\Interfaces;


interface OrderRepositoryInterface
{
    public function createOrder(array $data, $user);
}
