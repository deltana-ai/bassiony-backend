<?php

namespace App\Repositories;

use App\Helpers\Constants;
use App\Interfaces\Interfaces\ICrudRepository;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CrudRepository implements ICrudRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all($with = [], $conditions = [], $columns = array('*') , $customQuery = null)
    {
        $order_by = request(Constants::ORDER_BY) ?? "id";
        $deleted = request(Constants::Deleted) ?? false;
        
        $order_by_direction = request(Constants::ORDER_By_DIRECTION) ?? "asc";
        $filter_operator = request(Constants::FILTER_OPERATOR) ?? "=";
        $filters = request(Constants::FILTERS) ?? [];
        $per_page = request(Constants::PER_PAGE) ?? 15;
        $paginate = request(Constants::PAGINATE) ?? true;
        $query = $this->model;
        if ($deleted == true) {
            $query = $query->onlyTrashed();
        }

        $all_conditions = array_merge($conditions, $filters);
        foreach ($all_conditions as $key => $value) {
            if (is_numeric($value)) {
                $query = $query->where($key, '=', $value);
            } else {
                $query = $query->where($key, 'LIKE', '%' . $value . '%');
            }
        }
        if (is_callable($customQuery)) 
           $query = $customQuery($query);
       
        if (isset($order_by) && !empty($with))
            $query = $query->with($with)->orderBy($order_by, $order_by_direction);
        if ($paginate && !empty($with))
            return $query->with($with)->paginate($per_page, $columns);
        if (isset($order_by))
            $query = $query->orderBy($order_by, $order_by_direction);
        if ($paginate)
            return $query->paginate($per_page, $columns);
        
    
    
        if (!empty($with))
            return $query->with($with)->get($columns);
        else
            return $query->get($columns);
    }

    // Eager load database relationships
    public function AddMediaCollection($name = 'media', $model, $collection = 'default')
    {
        $oldMedia = $model->media()->where('collection', $collection)->first();
        if ($oldMedia) {
            $model->media()->detach($oldMedia->id);
        }
        $model->media()->attach([isset(request()->get($name)['id']) ? request()->get($name)['id'] : request()->get($name) => ['collection' => $collection]]);
    }


    // Eager load database relationships
    public function AddMediaCollectionArray($name = 'media', $model, $collection = 'default')
    {
        $oldMedia = $model->media()->where('collection', $collection)->get();
        if ($oldMedia->count() > 0) {
            foreach ($oldMedia as $key => $value) {
                $model->media()->detach($value->id);
            }
        }
        foreach (request()->get($name) as $key => $value) {
            $model->media()->attach([isset($value['id']) ? $value['id'] : $value => ['collection' => $collection]]);
        }
    }

    public function update(array $data, $id, $attribute = "id")
    {
        return $this->model->where($attribute, '=', $id)->update($data);
    }

    public function create(array $data)
    {
        $filteredData = collect($data)->toArray();
        return $this->model->create($filteredData);
    }

    // Set the associated model
    public function destroy($model)
    {
        $model->delete();
        return $model;
    }

    public function deleteRecords($tableName, $ids, $relationsToNeglect = [])
    {
        $destroyDenied = [];

        if (Schema::hasColumn($tableName, 'deleted_at')) {
            DB::table($tableName)
                ->whereIn('id', $ids)
                ->update(['deleted_at' => Carbon::now()]);
        } else {
            DB::table($tableName)->whereIn('id', $ids)->delete();
        }

        return count($destroyDenied);
    }






    public function deleteRecordsFinial($modelClass, $ids, $relationsToNeglect = [])
    {
        $destroyDenied = [];
        $modelClass::whereIn('id', $ids)->forceDelete();
        return count($destroyDenied);
    }




    public function restoreItem($modelName, $ids, $relationsToNeglect = [])
    {
        $destroyDenied = [];
        $modelName::whereIn('id', $ids)->restore();
        return count($destroyDenied);
    }



    public function restore($model)
    {
        $model->restore();
        return $model;
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }


    public function findInAll($id)
    {
        return  $this->model->withTrashed()->findOrFail($id);
    }

    // remove record from the database

    public function find($id)
    {
        return $this->model->findOrFail($id);
    }

    // remove record from the database

    public function findTrashed($id)
    {
        return $this->model->onlyTrashed()->findOrFail($id);
    }




        public function blackList($with = [], $conditions = [], $columns = array('*'), $useNetworkId = false, $useCollection = false)
    {
        $order_by = request(Constants::ORDER_BY) ?? "id";
        $deleted = request(Constants::Deleted) ?? false;
        $order_by_direction = request(Constants::ORDER_By_DIRECTION) ?? "asc";
        $filters = request(Constants::FILTERS) ?? [];
        $per_page = request(Constants::PER_PAGE) ?? 15;
        $paginate = request(Constants::PAGINATE) ?? false;
        $query = $this->model;

        if ($deleted == true) {
            $query = $query->onlyTrashed();
        }
        if (!empty($with)) {
            $query = $query->with($with);
        }
        $query = User::whereHas('networks', function ($query) {
            $query->where('status', 'blacklisted');
        })->where('active_member', 1);

        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (is_numeric($value)) {
                    $query = $query->where($key, '=', $value);
                } else {
                    $query = $query->where($key, 'LIKE', '%' . $value . '%');
                }
            }
        }
        if (isset($order_by)) {
            $query = $query->orderBy($order_by, $order_by_direction);
        }
        if ($paginate) {
            return $query->paginate($per_page, $columns);
        } else {
            return $query->get($columns);
        }
    }

    public function allcontact($with = [], $conditions = [], $columns = ['*'], $useNetworkId = false, $useCollection = false)
    {
        $networkId = request()->header('X-Network-ID');
        $currentNetworkCollect = User::where('id', $networkId)->value('collection');
        $order_by = request(Constants::ORDER_BY) ?? "id";
        $deleted = request(Constants::Deleted) ?? false;
        $order_by_direction = request(Constants::ORDER_By_DIRECTION) ?? "asc";
        $filter_operator = request(Constants::FILTER_OPERATOR) ?? "Like";
        $filters = request(Constants::FILTERS) ?? [];
        $per_page = request(Constants::PER_PAGE) ?? 15;
        $paginate = request(Constants::PAGINATE) ?? false;
        $relationData = request('relation');
        $networks = request('networks');
        $query = $this->model;

        if ($deleted == true) {
            $query = $query->onlyTrashed();
        }

        if (!empty($with)) {
            $query = $query->with($with);
        }

        if ($deleted == false) {
            if (!empty($relationData)) {
                foreach ($relationData as $relation) {
                    $name_relation = $relation['name_relation'];
                    $name_in_relation = $relation['name_in_relation'];
                    $filed_in_relation = $relation['filed_in_relation'];
                    if (isset($networks_active))
                        $networks_active = $relation['networks_active'];
                    if ($filed_in_relation == 'active' and $name_relation == 'networks') {
                        $relationMethod = $query->getModel()->$name_relation();
                        $relationTable = $relationMethod->getTable();
                        $query = $query->orWhereHas($name_relation, function ($q) use ($name_in_relation, $filed_in_relation, $relationTable) {
                            $q->where("$relationTable.$filed_in_relation", $name_in_relation)->where('network_id', request()->header('X-Network-ID'));
                        });
                    } else {
                        $query = $query->orWhereHas($name_relation, function ($q) use ($name_in_relation, $filed_in_relation, $relation, $networks) {
                            $networks_active = $relation['networks_active'] ?? null;
                            $networks = request('networks') ?? null;
                            if (is_string($name_in_relation)) {
                                if (isset($networks_active)) {
                                    $q->where($filed_in_relation, 'LIKE', '%' . $name_in_relation . '%')->where('network', $networks_active)->whereIn('network_id', $networks);
                                } else {
                                    $q->where($filed_in_relation, 'LIKE', '%' . $name_in_relation . '%');
                                }
                            } else {
                                $q->where($filed_in_relation, $name_in_relation);
                            }
                        });
                    }
                    foreach ($filters as $key => $value) {
                        if (is_numeric($value)) {
                            $query = $query->where($key, '=', $value);
                        } else {
                            $query = $query->where($key, 'LIKE', '%' . $value . '%');
                        }
                    }
                }
            }

            // Add your raw SQL query here
            $query = $query->join('users', 'contact_people.user_id', '=', 'users.id')
                ->whereNull('users.deleted_at')
                ->join('users_networks', 'users.id', '=', 'users_networks.user_id')
                ->whereIn('users_networks.status', ['approved', 'suspended'])
                ->where('users_networks.network_id', '=', $networkId)
                ->select('contact_people.*');
        } else {
            $query = $query->onlyTrashed();
            foreach ($filters as $key => $value) {
                if (is_numeric($value)) {
                    $query = $query->where($key, '=', $value);
                } else {
                    $query = $query->where($key, 'LIKE', '%' . $value . '%');
                }
            }
        }

        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (is_numeric($value)) {
                    $query = $query->where($key, '=', $value);
                } else {
                    $query = $query->where('contact_people.' . $key, 'LIKE', '%' . $value . '%');
                }
            }
        }
        if (isset($order_by)) {
            $query = $query->orderBy($order_by, $order_by_direction);
        }
        if ($useNetworkId) {
            if ($useCollection) {
                if (!$currentNetworkCollect) {
                    $query = $query->where('network_id', $networkId);
                }
            } else {
                $query = $query->where('network_id', $networkId);
            }
        }
        if ($paginate) {
            return $query->paginate($per_page, $columns);
        } else {
            return $query->get($columns);
        }
    }





    public function allUser($with = [], $conditions = [], $columns = ['*'], $useNetworkId = false, $useCollection = false)
    {
        $networkId = request()->header('X-Network-ID');
        $currentNetworkCollect = User::where('id', $networkId)->value('collection');
        $order_by = request(Constants::ORDER_BY) ?? "id";
        $deleted = request(Constants::Deleted) ?? false;
        $order_by_direction = request(Constants::ORDER_By_DIRECTION) ?? "asc";
        $filter_operator = request(Constants::FILTER_OPERATOR) ?? "Like";
        $filters = request(Constants::FILTERS) ?? [];
        $per_page = request(Constants::PER_PAGE) ?? 15;
        $paginate = request(Constants::PAGINATE) ?? false;
        $relationData = request('relation');
        $networks = request('networks');
        $network_users = request('network_filter');
        $query = $this->model;

        if ($this->model instanceof User) {
            $query = $query->where('active_member', 1);
        }

        $all_conditions = array_merge($conditions, $filters);

        if ($deleted == true) {
            $query = $query->onlyTrashed();
        }
        if (!empty($with)) {
            $query = $query->with($with);
        }
        if ($deleted == false) {
            if (!empty($relationData)) {

                foreach ($relationData as $relation) {
                    $name_relation = $relation['name_relation'];
                    $name_in_relation = $relation['name_in_relation'];
                    $filed_in_relation = $relation['filed_in_relation'];
                    if (isset($networks_active))
                        $networks_active = $relation['networks_active'];
                    if ($filed_in_relation == 'active' and $name_relation == 'networks') {
                        $relationMethod = $query->getModel()->$name_relation();
                        $relationTable = $relationMethod->getTable();
                        $query = $query->orWhereHas($name_relation, function ($q) use ($name_in_relation, $filed_in_relation, $relationTable) {
                            $q->where("$relationTable.$filed_in_relation", $name_in_relation)->where('network_id', request()->header('X-Network-ID'));
                        });
                    } else {
                        $query = $query->orWhereHas($name_relation, function ($q) use ($name_in_relation, $filed_in_relation, $relation, $networks) {
                            $networks_active = $relation['networks_active'] ?? null;
                            $networks = request('networks') ?? null;
                            if (is_string($name_in_relation)) {
                                if (isset($networks_active)) {
                                    $q->where($filed_in_relation, 'LIKE', '%' . $name_in_relation . '%')->where('network', $networks_active)->whereIn('network_id', $networks);
                                } else {
                                    $q->where($filed_in_relation, 'LIKE', '%' . $name_in_relation . '%');
                                }
                            } else {
                                $q->where($filed_in_relation, $name_in_relation);
                            }
                        });
                    }
                    foreach ($filters as $key => $value) {
                        if (is_numeric($value)) {
                            $query = $query->where($key, '=', $value);
                        } else {
                            $query = $query->where($key, 'LIKE', '%' . $value . '%');
                        }
                    }
                }
            }
            if (!empty($network_users)) {
                $status = $network_users['status'];
                $type = isset($network_users['type']) ? $network_users['type'] : null;
                $network = isset($network_users['network'])  ? $network_users['network'] : null;
                $fpp = isset($network_users['fpp']) ? $network_users['fpp'] : null;
                $active = isset($network_users['active']) ? $network_users['active'] : null;
                $network_ids = isset($network_users['network_ids']) ? $network_users['network_ids'] : null;
                $query = $query->WhereHas('networks', function ($q) use ($network_ids, $type, $status, $network, $fpp, $active) {
                    if (isset($active)) {
                        $q->where('users_networks.active', "$active");
                    }
                    if (!empty($network_ids)) {
                        $q->whereIn('network_id', $network_ids);
                    }
                    if (!empty($status)) {
                        $q->whereIn('status', $status);
                    }
                    if (!empty($type)) {
                        $q->whereIn('type', $type);
                    }
                    if (isset($network)) {
                        $q->where('network', $network);
                    }
                    if (isset($fpp)) {
                        $q->where('fpp', $fpp);
                    }
                });
                foreach ($filters as $key => $value) {
                    if (is_numeric($value)) {
                        $query = $query->where($key, '=', $value);
                    } else {
                        $query = $query->where($key, 'LIKE', '%' . $value . '%');
                    }
                }
            }
        } else {
            $query = $query->onlyTrashed();
            foreach ($filters as $key => $value) {
                if (is_numeric($value)) {
                    $query = $query->where($key, '=', $value);
                } else {
                    $query = $query->where($key, 'LIKE', '%' . $value . '%');
                }
            }
        }
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (is_numeric($value)) {
                    $query = $query->where($key, '=', $value);
                } else {
                    $query = $query->where($key, 'LIKE', '%' . $value . '%');
                }
            }
        }
        if (isset($order_by)) {
            $query = $query->orderBy($order_by, $order_by_direction);
        }
        if ($useNetworkId) {
            if ($useCollection) {
                if (!$currentNetworkCollect) {
                    $query = $query->where('network_id', $networkId);
                }
            } else {
                $query = $query->where('network_id', $networkId);
            }
        }
        if ($paginate) {
            return $query->paginate($per_page, $columns);
        } else {
            return $query->get($columns);
        }
    }


    function allUpdateTable($id, $case, $key = null, $value = null)
    {
        switch ($case) {
            case ('previous'):
                if ($id == 0) {
                    $model = $this->model->orderby('id', 'desc')->firstOrFail();
                } else {
                    $model = $this->model->where('id', '<', $id)->when(isset($value), function ($query) use ($key, $value) {
                        return $query->where($key, $value);
                    })->orderby('id', 'desc')->firstOrFail();
                    break;
                }
                $model = $this->model->where('id', '<', $id)->when(isset($value), function ($query) use ($key, $value) {
                    return $query->where($key, $value);
                })->orderby('id', 'desc')->firstOrFail();
                break;

            case ('next'):
                $model = $this->model->where('id', '>', $id)->when(isset($value), function ($query) use ($key, $value) {
                    return $query->where($key, $value);
                })->orderby('id', 'asc')->firstOrFail();
                break;
            case ('first'):
                $model = $this->model->when(isset($value), function ($query) use ($key, $value) {
                    return $query->where($key, $value);
                })->firstOrFail();
                break;
            case ('last'):
                $model = $this->model->when(isset($value), function ($query) use ($key, $value) {
                    return $query->where($key, $value);
                })->orderBy('id', 'desc')->first();
                break;
        }
        return $model;
    }

    function logInfo($abilitieId, $networkId = null, $date = null)
    {
        if (!$date) $date = Carbon::now()->format('Y-m-d');

        $q = "SELECT t1.network_id , t1.abilities, IFNULL(tOpening.tokenable_type,0) AS tokenable_type,IFNULL(tAdd.tokenable_id,0) as Add_balance,IFNULL(tIssue.Issue_balance,0) as Issue_balance,(IFNULL(tOpening.opening_balance,0) + IFNULL(tAdd.Add_balance,0) - IFNULL(tIssue.Issue_balance,0) )as Item_balance
            FROM w_products_networks t1 left OUTER JOIN
            (SELECT network_id,abilities, SUM(tokenable_id) AS tokenable_type FROM personal_access_tokens
            Where id in (Select id from  last_used_at  Where type_Id = 5 AND expires_at <= " . $date . " )
            GROUP BY network_id,abilities) tOpening on t1.network_id = tOpening.network_id and t1.abilities = tOpening.abilities
            left OUTER JOIN
            (SELECT network_id,abilities, SUM(tokenable_id) AS tokenable_id FROM personal_access_tokens
            Where  id in (Select id from last_used_at  Where type_Id  in (2,3)  AND  expires_at <=  " . $date . ")
            GROUP BY network_id,abilities) tAdd on t1.network_id = tAdd.network_id and t1.abilities = tAdd.abilities
            left OUTER JOIN
            (SELECT network_id,abilities , SUM(tokenable_id) AS Issue_balance FROM personal_access_tokens
            Where  id in (Select id from  last_used_at  Where type_Id  in (1,4) AND  expires_at <=  " . $date . ")
            GROUP BY network_id,abilities) tIssue on t1.network_id = tIssue.network_id and t1.abilities = tIssue.abilities
            Where t1.abilities = $abilitieId";

        if (!empty($networkId)) $q  .=  " AND t1.network_id = $networkId";

        $q  .=  " Order by t1.network_id ;";

        $data = DB::select($q);
        return $data;
    }
}
