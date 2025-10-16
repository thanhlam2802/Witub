<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use App\Http\Requests\Admin\StoreSettingRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SettingController extends Controller
{
    protected SettingService $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    /**
     * Hiển thị form quản lý cài đặt chung (footer + bảo trì)
     */
    public function index(): View
    {
        $footer = $this->settingService->getFooter();
        $maintenance = $this->settingService->isMaintenance();

        return view('content.settings.index', compact('footer', 'maintenance'));
    }

    /**
     * Lưu cài đặt footer và trạng thái bảo trì
     */
    public function update(StoreSettingRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        // Cập nhật footer
        $footerData = $validatedData['footer'] ?? [];
        $this->settingService->updateFooter($footerData);

        // Cập nhật trạng thái bảo trì
        $maintenanceStatus = $validatedData['maintenance'] ?? false;
        $this->settingService->setMaintenance($maintenanceStatus);

        return redirect()->back()->with('success', 'Cài đặt đã được cập nhật thành công.');
    }
}
