<?php



namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Lấy tất cả bản ghi
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->all($columns);
    }

    /**
     * Tìm theo ID
     */
    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->model->find($id, $columns);
    }

    /**
     * Tạo bản ghi mới
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Cập nhật bản ghi
     */
    public function update(int $id, array $data): ?Model
    {
        $record = $this->find($id);
        if (!$record) {
            return null;
        }
        $record->update($data);
        return $record;
    }

    /**
     * Xóa bản ghi
     */
    public function delete(int $id): bool
    {
        $record = $this->find($id);
        return $record ? $record->delete() : false;
    }
}
