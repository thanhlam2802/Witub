<?php

use App\Http\Controllers\User\StudioController;
use App\Http\Controllers\Admin\SeoToolsController;
use App\Http\Controllers\Admin\CategoryTypeController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Cache;
use App\Models\Locale;
use App\Http\Middleware\SetAppLocale;
use App\http\Middleware\CheckMaintenanceMode;
/*
|--------------------------------------------------------------------------
| 🌐 FRONTEND ROUTES
|--------------------------------------------------------------------------
*/


Route::get('/', function () {
    $default = Cache::rememberForever('default_locale', function () {
        return Locale::where('is_default', true)->value('locale_code') ?? 'vi';
    });
    return redirect("/{$default}");
});
Route::get('/switch-language/{locale}', [LanguageController::class, 'switch'])
    ->name('language.switch');



Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => 'vi|en'],
    'middleware' => [CheckMaintenanceMode::class, SetAppLocale::class]
], function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::view('/privacy-policy', 'privacy-policy')->name('privacy');

    // Blog
    Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/{slug}', [BlogController::class, 'resolveSlug'])->name('blog.resolver');
});
/*
|--------------------------------------------------------------------------
| 🔐 AUTH ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    // Đăng xuất (giữ nguyên)
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');



    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('auth.login.view');

    Route::post('/login', [AuthController::class, 'handleLogin'])->name('auth.login');

    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('auth.register.view');

    Route::post('/register', [AuthController::class, 'handleRegistration'])->name('auth.register');


    // --- Các Route cho Google (giữ nguyên) ---
    Route::get('/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
    Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});
/*
|--------------------------------------------------------------------------
| 🎨 USER STUDIO ROUTE (THÊM MỚI)
|--------------------------------------------------------------------------
*/
Route::prefix('studio')
    ->middleware('auth')
    ->name('studio.')
    ->group(function () {


        Route::get('/', [StudioController::class, 'index'])->name('index');
    });


/*
|--------------------------------------------------------------------------
| 🧭 ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth', \App\Http\Middleware\IsAdmin::class])
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::resource('users', UserController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('locales', LocaleController::class)->except(['show']);
        Route::post('/posts/preview', [PostController::class, 'preview'])->name('admin.posts.preview');
        Route::resource('posts', PostController::class);
        Route::resource('category-types', CategoryTypeController::class);
        Route::group(['prefix' => 'settings', 'as' => 'settings.'], function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::put('/', [SettingController::class, 'update'])->name('update');
        });
        Route::get('posts/{post}/toggle/{attribute}', [\App\Http\Controllers\Admin\PostController::class, 'toggleStatus'])
            ->name('posts.toggleStatus')
            ->whereIn('attribute', ['status', 'is_featured']);

        Route::prefix('images')->name('images.')->group(function () {
            Route::get('/', [ImageController::class, 'index'])->name('index');
            Route::post('/upload', [ImageController::class, 'uploadFiles'])->name('upload');
            Route::post('/delete', [ImageController::class, 'deleteFiles'])->name('delete');
            Route::post('/create-folder', [ImageController::class, 'createFolder'])->name('createFolder');
            Route::post('/delete-folder', [ImageController::class, 'deleteFolder'])->name('deleteFolder');
        });
        Route::post('/upload/editor-image', [\App\Http\Controllers\Admin\ImageController::class, 'uploadFromEditor'])->name('upload.image');

        // === START: SEO TOOLS ROUTES ===
        Route::get('seo-tools', [SeoToolsController::class, 'index'])->name('admin.seo_tools.index');
        Route::post('seo-tools', [SeoToolsController::class, 'save'])->name('admin.seo_tools.save');
    });

// Thêm vào cuối file routes/web.php
Route::get('/test-layout', function () {
    return view('testpage');
});
