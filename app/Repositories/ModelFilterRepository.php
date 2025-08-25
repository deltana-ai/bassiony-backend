<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
class ModelFilterRepository
{
    /**
     * Apply common filters: search, active, relation (pharmacy/company), pagination
     */

    public function applyFilters(Builder $query, Request $request)
    {
        

        if (in_array('active', $query->getModel()->getFillable())) {
            $query->where('active', 1);
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $perPage = $request->get('per_page', 10);
        $orderBy = $request->get('order_by', 'id');
        $orderByDirection = $request->get('order_direction', 'asc');
        $table = $query->getModel()->getTable();
        $query->orderBy($orderBy, $orderByDirection);
        

        return $query->paginate($perPage);
    }

}
