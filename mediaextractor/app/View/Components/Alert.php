<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Alert extends Component
{
    public string $type;
    public string $title;
    public array $styles;

    /**
     * Create a new component instance.
     *
     * @param string $type Loại alert: 'info', 'success', 'danger', 'warning', 'dark'
     * @param string|null $title Tiêu đề của alert
     */
    public function __construct(string $type = 'info', ?string $title = null)
    {
        $this->type = $type;
        $this->styles = $this->getStyles($type);
        $this->title = $title ?? $this->getDefaultTitle($type);
    }

    /**
     * Lấy các class CSS tương ứng với từng loại alert.
     */
    private function getStyles(string $type): array
    {
        switch ($type) {
            case 'success':
                return ['text' => 'text-green-800', 'bg' => 'bg-green-50', 'dark_text' => 'dark:text-green-400'];
            case 'danger':
                return ['text' => 'text-red-800', 'bg' => 'bg-red-50', 'dark_text' => 'dark:text-red-400'];
            case 'warning':
                return ['text' => 'text-yellow-800', 'bg' => 'bg-yellow-50', 'dark_text' => 'dark:text-yellow-300'];
            case 'dark':
                return ['text' => 'text-gray-800', 'bg' => 'bg-gray-50', 'dark_text' => 'dark:text-gray-300'];
            case 'info':
            default:
                return ['text' => 'text-blue-800', 'bg' => 'bg-blue-50', 'dark_text' => 'dark:text-blue-400'];
        }
    }

    /**
     * Lấy tiêu đề mặc định nếu người dùng không cung cấp.
     */
    private function getDefaultTitle(string $type): string
    {
        switch ($type) {
            case 'success':
                return 'Thành công!';
            case 'danger':
                return 'Lỗi!';
            case 'warning':
                return 'Cảnh báo!';
            case 'dark':
                return 'Thông báo!';
            case 'info':
            default:
                return 'Thông tin!';
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.alert');
    }
}
