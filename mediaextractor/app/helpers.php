<?php

if (!function_exists('localized_route')) {
    function localized_route($routeName, $parameters = [], $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        $parameters = array_merge(['locale' => $locale], $parameters);
        return route($routeName, $parameters);
    }
}
if (!function_exists('localized_url')) {

    function localized_url($locale)
    {
        $route = request()->route();
        if (!$route) {
            return url($locale);
        }

        $params = $route->parameters();
        $params['locale'] = $locale;

        try {
            return route($route->getName(), $params);
        } catch (\Exception $e) {
            return url($locale);
        }
    }
}
