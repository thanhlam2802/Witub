<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/home')->with('error', 'Bạn cần đăng nhập.');
        }

        $user = Auth::user();

        // ✅ Nếu là admin -> cho phép truy cập toàn bộ
        if ($user->role === 'admin') {
            return $next($request);
        }

        // ✅ Nếu là poster -> chỉ cho phép truy cập vào trang posts
        if ($user->role === 'poster') {
            if ($request->is('admin/posts*')) {
                return $next($request);
            }
            return redirect('/admin/posts')->with('error', 'Bạn chỉ có quyền xem và quản lý bài viết.');
        }

        // ❌ Các role khác bị chặn
        return redirect('/home')->with('error', 'Bạn không có quyền truy cập trang này.');
    }
}
