<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
class EnterpriseRepository
{
    /**
     * Apply common filters: search, active, relation (pharmacy/company), pagination
     */

    public function applyFilters(Builder $query, Request $request, $relationId = null, $relationName = null)
    {
        if ($relationId && $relationName) {
            $query->whereHas($relationName, function ($q) use ($relationId) {
                $q->where($q->getModel()->getTable().'.id', $relationId);
            });
        }

        if (in_array('active', $query->getModel()->getFillable())) {
            $query->where('active', 1);
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $perPage = $request->get('per_page', 10);

        $table = $query->getModel()->getTable();
        
        if($request->filled('order_by') && $request->filled('order_direction')) {
            $orderBy = $request->get('order_by', 'id');
            $orderByDirection = $request->get('order_direction', 'asc');
            $query->orderBy($orderBy, $orderByDirection);
        }
        else{
            if (Schema::hasColumn($table, 'position')) {
              $query->orderBy('position', 'asc');
            } else {
              $query->orderBy('id', 'desc');
            }
        }
        

        return $query->paginate($perPage);
    }

}
