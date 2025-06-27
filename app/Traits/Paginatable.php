<?php

namespace App\Traits;

trait Paginatable
{
    public function paginateQuery($query)
    {
        $perPage = request()->get('per_page', 10); 
        return $query->paginate($perPage);
    }
}
