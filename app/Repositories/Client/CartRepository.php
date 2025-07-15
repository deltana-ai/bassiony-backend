<?php
namespace App\Repositories\Client;

use App\Models\Cart;
use App\Interfaces\Client\CartRepositoryInterface;
use App\Repositories\CrudRepository;
use Illuminate\Database\Eloquent\Model;

class CartRepository extends CrudRepository implements CartRepositoryInterface
{
  protected Model $model;

  public function __construct(Cart $model)
  {
      $this->model = $model;
  }

}
