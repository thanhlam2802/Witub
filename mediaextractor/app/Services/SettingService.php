<?php

namespace App\Services;

use App\Repositories\SettingRepository;

class SettingService extends BaseService
{
    protected SettingRepository $settingRepository;

    public function __construct(SettingRepository $settingRepository)
    {
        $this->settingRepository = $settingRepository;
    }

    /**
     * Lấy tất cả cài đặt và chuyển đổi thành mảng key => value
     */
    public function getAllSettings(): array
    {
        return $this->settingRepository->all()
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Cập nhật các cài đặt từ form
     * @param array $data Dữ liệu key => value từ request
     */
    public function updateSettings(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->settingRepository->updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
