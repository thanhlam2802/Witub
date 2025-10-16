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

    public function switch(Request $request, $newLocale)
    {

        if (!in_array($newLocale, config('app.available_locales', ['vi', 'en']))) {
            abort(400, 'Ngôn ngữ không hợp lệ');
        }


        App::setLocale($newLocale);
        session()->put('locale', $newLocale);


        $previousUrl = url()->previous();
        try {

            $previousRoute = Route::getRoutes()->match(Request::create($previousUrl));
            $routeName = $previousRoute->getName();
            $routeParams = $previousRoute->parameters();
        } catch (\Exception $e) {

            return Redirect::to("/{$newLocale}");
        }


        if ($routeName === 'blog.resolver' && isset($routeParams['locale']) && isset($routeParams['slug'])) {

            $oldLocale = $routeParams['locale'];
            $oldSlug = $routeParams['slug'];
            $model = null;


            $model = Category::whereHas('translations', function ($q) use ($oldSlug, $oldLocale) {
                $q->where('slug', $oldSlug)->where('locale_code', $oldLocale);
            })->first();

            if (!$model) {
                $model = Post::whereHas('translations', function ($q) use ($oldSlug, $oldLocale) {
                    $q->where('slug', $oldSlug)->where('locale_code', $oldLocale);
                })->first();
            }

            if ($model) {

                $translation = $model->translations()->where('locale_code', $newLocale)->first();

                if ($translation && $translation->slug) {

                    $routeParams['locale'] = $newLocale;
                    $routeParams['slug'] = $translation->slug;
                    return Redirect::route($routeName, $routeParams);
                }
            }

            return Redirect::route('blog.index', ['locale' => $newLocale]);
        }


        if (isset($routeParams['locale'])) {
            $routeParams['locale'] = $newLocale;
            return Redirect::route($routeName, $routeParams);
        }


        return Redirect::to("/{$newLocale}");
    }
}
