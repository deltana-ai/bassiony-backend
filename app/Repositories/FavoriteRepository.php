<?php

namespace App\Repositories;

use App\Interfaces\FavoriteRepositoryInterface;
use App\Models\Favorite;

use Illuminate\Database\Eloquent\Model;

class FavoriteRepository extends CrudRepository implements FavoriteRepositoryInterface
{
    protected Model $model;

    public function __construct(Favorite $model)
    {
        $this->model = $model;
    }
}

