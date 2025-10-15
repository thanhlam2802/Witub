<?php

namespace App\Services;

use App\Repositories\CategoryTypeRepository;
use App\Models\CategoryType;
use Illuminate\Database\Eloquent\Collection;

class CategoryTypeService
{
    protected $categoryTypeRepository;

    public function __construct(CategoryTypeRepository $categoryTypeRepository)
    {
        $this->categoryTypeRepository = $categoryTypeRepository;
    }

    public function getAllActive(): Collection
    {
        return $this->categoryTypeRepository->getAllActive();
    }

    public function findById(int $id): ?CategoryType
    {
        return $this->categoryTypeRepository->find($id);
    }

    public function create(array $data): CategoryType
    {
        return $this->categoryTypeRepository->create($data);
    }

    public function update(CategoryType $categoryType, array $data): CategoryType
    {
        // ✅ Dùng model thay vì id
        $categoryType->update($data);
        return $categoryType;
    }

    public function delete(CategoryType $categoryType): bool
    {
        return $categoryType->delete();
    }
}
