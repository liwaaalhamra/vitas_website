<?php

namespace App\Http\Middleware;

use Closure;
use View;
use App;
use App\SeoMetaTag;
use App\ContactInfo;

class WebsiteMiddleware
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
        // Get locale
        $locale = App::getLocale();

        // Get SEO metadata
        $seo = SeoMetaTag::select([
            'title',
            'description',
            'keywords'
        ])->first();

        // Get Footer Contact Info
        $contactFooter = [];
        $contactFooter_db = ContactInfo::select([
            'slug',
            'value_' . $locale . ' as value'
        ])->get();
        foreach ($contactFooter_db as $contact_db) $contactFooter[$contact_db['slug']] = $contact_db['value'];

        // Share variables
        View::share(compact('seo', 'contactFooter'));

        return $next($request);
    }
}
