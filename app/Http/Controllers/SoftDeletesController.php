<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\Profile;
use App\Models\User;
use App\Traits\CaptureIpTrait;
use Auth;
use Illuminate\Http\Request;
use dbmigration\LaravelRoles\Models\Role;

class SoftDeletesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * отримати видалених юзерів
     */
    public static function getDeletedUser($id)
    {
        $user = User::onlyTrashed()->where('id', $id)->get();
        if (count($user) != 1) {
            return redirect('/users/deleted/')->with('error', trans('usersmanagement.errorUserNotFound'));
        }
        return $user[0];
    }

    /**
     * подивитися видалених юзерів
     *
     */
    public function index()
    {
        $users = User::onlyTrashed()->get();
        $roles = Role::all();
        return View('usersmanagement.show-deleted-users', compact('users', 'roles'));
    }

    /**
     *подивитися вибраного видаленого юзера
     *
     */
    public function show($id)
    {
        $user = self::getDeletedUser($id);
        return view('usersmanagement.show-deleted-user')->withUser($user);
    }

    /**
     * оновити юзера
     *
     */
    public function update(Request $request, $id)
    {
        $user = self::getDeletedUser($id);
        $user->restore();
        return redirect('/users/')->with('success', trans('usersmanagement.successRestore'));
    }

    /**
     * видалити юзера
     *
     */
    public function destroy($id)
    {
        $user = self::getDeletedUser($id);
        $user->forceDelete();
        return redirect('/users/deleted/')->with('success', trans('usersmanagement.successDestroy'));
    }

}
