<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {

        $user = Auth::user();

        if ($user->isAdmin()) {

            return view('pages.admin.home');

        }

        return view('pages.user.home');

    }

}