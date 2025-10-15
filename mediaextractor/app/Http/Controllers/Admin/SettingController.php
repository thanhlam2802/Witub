<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Request;
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
     * Hiển thị form quản lý cài đặt.
     */
    public function index(): View
    {
        $settings = $this->settingService->getAllSettings();
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Xử lý việc lưu cài đặt.
     */
    public function update(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([]);

        $this->settingService->updateSettings($validatedData);

        return back()->with('success', 'Cài đặt đã được cập nhật thành công.');
    }
}
