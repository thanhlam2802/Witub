<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Repositories\CategoryTypeRepository;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(CategoryTypeRepository $categoryTypeRepository): void
    {
        // 1. Composer cho Sidebar (Lấy danh sách Category Types)
        View::composer('partials.sidebar', function ($view) use ($categoryTypeRepository) {
            $categoryTypes = $categoryTypeRepository->getAllActive();
            $view->with('categoryTypes', $categoryTypes);
        });

        // 2. Composer cho Sidebar (Xử lý trạng thái Active và Group)
        View::composer('partials.sidebar', function ($view) {
            $group = '';
            $page_slug = '';
            $routeName = Route::currentRouteName();
            $request = request(); // Lấy request hiện tại để kiểm tra query parameters

            if (str_starts_with($routeName, 'posts.')) {
                $group = 'crud';
                $page_slug = 'posts';
            } elseif (str_starts_with($routeName, 'users.')) {
                $group = 'crud';
                $page_slug = 'users';
            } elseif (str_starts_with($routeName, 'locales.')) {
                $group = 'crud';
                $page_slug = 'locales';
            }
            // Xử lý cho trang quản lý các loại danh mục
            elseif (str_starts_with($routeName, 'category-types.')) {
                $group = 'layout';
                $page_slug = 'category-types';
            }
            // Xử lý cho trang danh mục
            elseif (str_starts_with($routeName, 'categories.')) {
                $group = 'layout';
                if (!$request->has('type')) {
                    $page_slug = 'categories';
                }
            } elseif (str_starts_with($routeName, 'admin.seo_tools.')) {
                $group = 'page';
                $page_slug = 'seo-tools';
            } elseif (str_starts_with($routeName, 'admin.settings.')) {
                $group = 'page';
                $page_slug = 'settings';
            }

            $view->with(compact('group', 'page_slug'));
        });


        // 3. Composer cho Breadcrumbs (KHẮC PHỤC LỖI LOCALE)
        View::composer('*', function ($view) {
            $routeName = Route::currentRouteName();
            $breadcrumbs = [];
            $currentLocale = app()->getLocale(); // ✅ Lấy locale hiện tại

            if ($routeName) {
                // Logic cho SEO Tools
                if (str_starts_with($routeName, 'admin.seo_tools.')) {
                    $breadcrumbs[] = ['label' => 'Dashboard', 'url' => route('admin.dashboard')];
                    $breadcrumbs[] = ['label' => 'SEO Tools'];
                }
                // Logic cho các route khác
                else {
                    $parts = explode('.', $routeName);
                    $module = ucfirst($parts[0]);
                    $action = $parts[1] ?? '';

                    // ✅ KHẮC PHỤC LỖI: Luôn truyền tham số 'locale' khi tạo URL
                    $url = Route::has($parts[0] . '.index')
                        // Nếu là route có tham số locale (như blog.index), ta truyền locale hiện tại
                        ? route($parts[0] . '.index', ['locale' => $currentLocale])
                        // Nếu không phải route index, dùng dashboard làm fallback
                        : route('admin.dashboard');


                    $breadcrumbs[] = [
                        'label' => $module,
                        'url' => $url,
                    ];

                    switch ($action) {
                        case 'index':
                            $breadcrumbs[] = ['label' => 'List'];
                            break;
                        case 'create':
                            $breadcrumbs[] = ['label' => 'Create'];
                            break;
                        case 'edit':
                            $breadcrumbs[] = ['label' => 'Edit'];
                            break;
                        case 'show':
                            $breadcrumbs[] = ['label' => 'Details'];
                            break;
                        default:
                            if (!empty($action)) {
                                $breadcrumbs[] = ['label' => ucfirst($action)];
                            }
                            break;
                    }
                }
            }

            $view->with('breadcrumbs', $breadcrumbs);
        });
    }
}
