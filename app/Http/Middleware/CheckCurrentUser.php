<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class CheckCurrentUser
{
    public function handle($request, Closure $next)
    {

        if (!$request->user())
        {
            abort(403, 'Несанкціонована дія!');
        }

        return $next($request);

    }

    public function terminate($request, $response)
    {

        $user           = Auth::user();
        $currentRoute   = Route::currentRouteName();
        Log::info('CheckCurrentUser middlware was used: ' . $currentRoute . '. ', [$user]);

    }

}



