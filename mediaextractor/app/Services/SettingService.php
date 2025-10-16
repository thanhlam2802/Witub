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

    public function getAllSettings(): array
    {
        return $this->settingRepository->all()
            ->pluck('value', 'key')
            ->toArray();
    }

    public function updateSettings(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->settingRepository->updateOrCreate(
                ['key' => $key],

                ['value' => $value]
            );
        }
    }


    public function getFooter(): array
    {
        $footer = $this->settingRepository->findByKey('footer');

        $footerData = $footer->value ?? [];


        $defaultFooter = [
            'logo' => '/images/witub-logo.svg',
            'year' => date('Y'),
            'address' => '',
            'hotline' => '',
            'description' => 'Witub là một nền tảng cung cấp các công cụ trực tuyến hữu ích, giúp bạn làm việc nhanh chóng và hiệu quả hơn.',
            'email' => '',

            'menu_columns' => [
                [
                    'title' => 'Công cụ',
                    'items' => [
                        ['text' => 'Tên Tool A', 'url' => '/tools/tool-a'],
                        ['text' => 'Xem tất cả', 'url' => '/tools'],
                    ]
                ],
                [
                    'title' => 'Hỗ trợ',
                    'items' => [
                        ['text' => 'Câu hỏi thường gặp (FAQ)', 'url' => '/faq'],
                        ['text' => 'Liên hệ hỗ trợ', 'url' => '/contact'],
                    ]
                ],
                [
                    'title' => 'Pháp lý',
                    'items' => [
                        ['text' => 'Chính sách bảo mật', 'url' => '/privacy-policy'],
                        ['text' => 'Điều khoản dịch vụ', 'url' => '/terms-of-service'],
                    ]
                ],
            ]

        ];


        return array_merge($defaultFooter, is_array($footerData) ? $footerData : []);
    }

    public function updateFooter(array $data): void
    {
        $this->settingRepository->updateOrCreate(
            ['key' => 'footer'],
            ['value' => $data]
        );
    }

    public function isMaintenance(): bool
    {
        $value = $this->settingRepository->findByKey('site_maintenance')->value ?? 'false';
        return $value === 'true';
    }

    public function setMaintenance(bool $status): void
    {
        $this->settingRepository->updateOrCreate(
            ['key' => 'site_maintenance'],

            ['value' => $status ? 'true' : 'false']
        );
    }
}
