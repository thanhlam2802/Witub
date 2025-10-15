<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use App\Models\Category;
use App\Models\Post;

class LanguageController extends Controller
{
    /**
     * Chuyển đổi ngôn ngữ và chuyển hướng đến URL đã được dịch.
     */
    public function switch(Request $request, $newLocale)
    {
        // 1. Chỉ chấp nhận các locale hợp lệ
        if (!in_array($newLocale, config('app.available_locales', ['vi', 'en']))) {
            abort(400, 'Ngôn ngữ không hợp lệ');
        }

        // 2. Lưu ngôn ngữ mới vào session
        App::setLocale($newLocale);
        session()->put('locale', $newLocale);

        // 3. Phân tích URL của trang trước đó
        $previousUrl = url()->previous();
        try {
            $previousRoute = Route::getRoutes()->match(Request::create($previousUrl));
            $routeName = $previousRoute->getName();
            $routeParams = $previousRoute->parameters();
        } catch (\Exception $e) {
            return Redirect::to("/{$newLocale}");
        }

        // 4. Xử lý chuyển hướng cho các route có slug cần dịch
        if (isset($routeParams['locale']) && isset($routeParams['slug'])) {
            $oldLocale = $routeParams['locale'];
            $oldSlug = $routeParams['slug'];
            $model = null;

            // ✅ SỬA LỖI: Kiểm tra cả Category và Post cho route 'blog.resolver'
            if ($routeName === 'blog.resolver') {
                // Ưu tiên tìm trong danh mục trước
                $model = Category::whereHas('translations', fn($q) => $q->where('slug', $oldSlug)->where('locale_code', $oldLocale))->first();

                // Nếu không phải danh mục, tìm trong bài viết
                if (!$model) {
                    $model = Post::whereHas('translations', fn($q) => $q->where('slug', $oldSlug)->where('locale_code', $oldLocale))->first();
                }
            }

            if ($model) {
                // Tìm bản dịch của model trong ngôn ngữ mới
                $translation = $model->translations()->where('locale_code', $newLocale)->first();

                if ($translation && $translation->slug) {
                    // Nếu tìm thấy, cập nhật slug và chuyển hướng
                    $routeParams['locale'] = $newLocale;
                    $routeParams['slug'] = $translation->slug;
                    return Redirect::route($routeName, $routeParams);
                }
            }

            // Fallback: Nếu không tìm thấy model hoặc bản dịch, chuyển về trang blog chính
            return Redirect::route('blog.index', ['locale' => $newLocale]);
        }

        // 5. Fallback chung cho các trang không có slug (ví dụ /vi/blog -> /en/blog)
        if (isset($routeParams['locale'])) {
            $routeParams['locale'] = $newLocale;
            return Redirect::route($routeName, $routeParams);
        }

        return Redirect::to("/{$newLocale}");
    }
}
