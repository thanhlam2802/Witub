<?php

namespace App\Repositories;

use App\Models\CategoryType;
use Illuminate\Database\Eloquent\Collection;

class CategoryTypeRepository extends BaseRepository
{
    public function __construct(CategoryType $model)
    {
        parent::__construct($model);
    }


    public function getAllActive(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();
    }
}
