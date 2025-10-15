<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{

    /**
     * Middleware để bảo vệ các route của admin.
     * Chỉ những user đã đăng nhập và có role là 'admin' mới vào được.
     */
    public function __construct() {}

    /**
     * Hiển thị trang dashboard chính của admin.
     */
    public function index(): View
    {

        return view('admin.dashboard');
    }
}
