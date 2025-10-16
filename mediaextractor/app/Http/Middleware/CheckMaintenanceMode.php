<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\SettingService;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    protected SettingService $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Kiểm tra trạng thái bảo trì có được bật không
        if ($this->settingService->isMaintenance()) {

            if (Auth::check() && Auth::user()->is_admin) {
                return $next($request);
            }


            if ($request->is('admin*')) {
                return $next($request);
            }


            return response()->view('maintenance', [], 503);
        }


        return $next($request);
    }
}
