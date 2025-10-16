<?php

namespace App\Repositories;

use App\Models\Setting;

class SettingRepository extends BaseRepository
{
    public function __construct(Setting $model)
    {
        parent::__construct($model);
    }

    /**
     * Cập nhật hoặc tạo bản ghi nếu chưa tồn tại
     *
     * @param array $attributes Điều kiện tìm bản ghi
     * @param array $values Dữ liệu cập nhật hoặc tạo mới
     * @return Setting
     */
    public function updateOrCreate(array $attributes, array $values): Setting
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    /**
     * Tìm theo key
     *
     * @param string $key
     * @return Setting|null
     */
    public function findByKey(string $key): ?Setting
    {
        return $this->model->where('key', $key)->first();
    }
}
