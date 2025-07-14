<?php
namespace App\Repositories\Client;

use App\Models\Cart;
use App\Interfaces\CartRepositoryInterface;

use Illuminate\Database\Eloquent\Model;

class CartRepository extends CrudRepository
{
  protected Model $model;

  public function __construct(Cart $model)
  {
      $this->model = $model;
  }

}
