<?php

namespace App\Http\Middleware;

use Closure;
use App;
use View;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Get locale from URL and check if it is allowed
        $allowed_locales = ['en', 'ar'];

        // Get locale from URL
        if(substr(request()->path(), 0, 3) == "api"){
            $locale = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        } else {
            $locale = substr(request()->path(), 0, 2);
        }

        // Check if locale is valid
        if (!in_array($locale, $allowed_locales)) abort(404);

        // Set locale
        App::setLocale($locale);

        // Share locale with all views
        View::share(compact('locale'));

        return $next($request);
    }
}
