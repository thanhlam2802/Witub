<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Middleware yêu cầu người dùng phải đăng nhập để truy cập.
     */
    public function __construct() {}

    /**
     * Hiển thị trang chủ cho người dùng đã đăng nhập.
     */
    public function index(): View
    {

        return view('layouts.app');
    }
}
