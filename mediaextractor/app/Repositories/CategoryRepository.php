<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

use App\Models\CategoryType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryRepository extends BaseRepository
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }


    public function createWithTranslations(array $categoryData, array $translationsData): Category
    {
        return DB::transaction(function () use ($categoryData, $translationsData) {
            $category = $this->model->create($categoryData);

            foreach ($translationsData as $locale => $data) {
                $category->translations()->create(array_merge($data, ['locale_code' => $locale]));
            }

            return $category;
        });
    }


    public function updateWithTranslations(int $id, array $categoryData, array $translationsData): Category
    {
        return DB::transaction(function () use ($id, $categoryData, $translationsData) {
            $category = $this->find($id);
            $category->update($categoryData);

            foreach ($translationsData as $locale => $data) {
                $category->translations()->updateOrCreate(
                    ['locale_code' => $locale],
                    $data
                );
            }

            return $category;
        });
    }


    public function getPaginatedWithTranslations(int $perPage = 15)
    {
        return $this->model->with('translations', 'type')->latest()->paginate($perPage);
    }


    public function searchAndPaginate(int $perPage = 15, ?string $search = null, ?int $typeId = null): LengthAwarePaginator
    {
        $query = $this->model->query()->with(['translations', 'type']);

        // Lọc theo từ khóa tìm kiếm
        if ($search) {
            $query->whereHas('translations', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // THÊM MỚI: Lọc theo Category Type ID
        if ($typeId) {
            $query->where('type_id', $typeId);
        }

        return $query->latest()->paginate($perPage);
    }


    /**
     * Chỉ lấy các mục cha, và eager-load các mục con.
     *
     * @return Collection
     */
    public function getCategoryTree(): Collection
    {
        return $this->model
            ->whereNull('parent_id')
            ->with([
                'translations',
                'children' => function ($query) {
                    $query->with('translations', 'children.translations')->orderBy('sort_order', 'asc');
                }
            ])
            ->orderBy('sort_order', 'asc')
            ->get();
    }


    public function getCategoryTreeByType(string $typeCode): Collection
    {

        $type = CategoryType::where('code', $typeCode)->first();


        if (!$type) {
            return new Collection();
        }


        return $this->model
            ->where('type_id', $type->id)
            ->whereNull('parent_id')
            ->with([
                'translations',
                'children' => function ($query) {
                    $query->with('translations', 'children.translations')->orderBy('sort_order', 'asc');
                }
            ])
            ->orderBy('sort_order', 'asc')
            ->get();
    }
}
