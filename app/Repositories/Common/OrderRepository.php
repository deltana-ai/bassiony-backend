<?php
namespace App\Repositories\Client;

use App\Models\Order;
use App\Interfaces\Common\OrderRepositoryInterface;
use App\Repositories\CrudRepository;
use Illuminate\Database\Eloquent\Model;

class CartRepository extends CrudRepository implements OrderRepositoryInterface
{
  protected Model $model;

  public function __construct(Order $model)
  {
      $this->model = $model;
  }

}
