<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class Authenticate
{
    protected $auth;


    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    public function handle($request, Closure $next, $role)
    {
        if(!$this->auth->check())
        {
            return redirect()->to('/login')
                ->with('status', 'success')
                ->with('message', 'Please login.');
        }

        return $next($request);

    }

    public function terminate($request, $response)
    {

        $user           = Auth::user();
        $currentRoute   = Route::currentRouteName();
        Log::info('Authenticate middlware was used: ' . $currentRoute . '. ', [$user]);

    }


}