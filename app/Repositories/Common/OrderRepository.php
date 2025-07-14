<?php
namespace App\Repositories\Client;

use App\Models\Order;
use App\Interfaces\CartRepositoryInterface;

use Illuminate\Database\Eloquent\Model;

class CartRepository extends CrudRepository
{
  protected Model $model;

  public function __construct(Order $model)
  {
      $this->model = $model;
  }

}
