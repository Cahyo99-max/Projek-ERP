<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->segment(1);
        $available = ['en', 'id'];
        if (in_array($locale, $available)) {
            App::setLocale($locale);
            session(['locale' => $locale]);
        } elseif (session()->has('locale')) {
            App::setLocale(session('locale'));
        }
        return $next($request);
    }
}
