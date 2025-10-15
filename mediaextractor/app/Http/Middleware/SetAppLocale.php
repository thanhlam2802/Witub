<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use App\Models\Locale;

class SetAppLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Lấy locale từ URL
        $locale = $request->segment(1);

        // Lấy danh sách locale hợp lệ từ DB
        $availableLocales = Cache::rememberForever('available_locales', function () {
            return Locale::where('is_active', true)->pluck('locale_code')->toArray();
        });

        // Nếu không hợp lệ → lấy từ session
        if (!in_array($locale, $availableLocales)) {
            $locale = Session::get('locale', Cache::rememberForever('default_locale', function () {
                return Locale::where('is_default', true)->value('locale_code') ?? 'vi';
            }));
        }

        // Gán locale
        App::setLocale($locale);
        Session::put('locale', $locale);

        return $next($request);
    }
}
