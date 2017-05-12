<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Theme;
use App\Models\User;
use App\Notifications\SendGoodbyeEmail;
use App\Traits\CaptureIpTrait;
use File;
use Helper;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Image;
use Webpatser\Uuid\Uuid;
use Validator;
use View;

class ProfilesController extends Controller
{

    protected $idMultiKey     = '618423'; //int
    protected $seperationKey  = '****';

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * валідація даних
     *
     */
    public function profile_validator(array $data)
    {
        return Validator::make($data, [
            'theme_id'          => '',
            'location'          => '',
            'bio'               => 'max:500',
            'twitter_username'  => 'max:50',
            'github_username'   => 'max:50',
            'avatar'            => '',
            'avatar_status'     => '',
        ]);
    }

    public function getUserByUsername($username)
    {
        return User::with('profile')->wherename($username)->firstOrFail();
    }

    /**
     *показати юзера
     *
     */
    public function show($username)
    {
        try {

            $user = $this->getUserByUsername($username);

        } catch (ModelNotFoundException $exception) {

            abort(404);

        }

        $currentTheme = Theme::find($user->profile->theme_id);

        $data = [
            'user' => $user,
            'currentTheme' => $currentTheme
        ];

        return view('profiles.show')->with($data);

    }

    /**
     * /profiles/username/edit
     */
    public function edit($username)
    {
        try {

            $user = $this->getUserByUsername($username);

        } catch (ModelNotFoundException $exception) {
            return view('pages.status')
                ->with('error', trans('profile.notYourProfile'))
                ->with('error_title', trans('profile.notYourProfileTitle'));
        }

        $themes = Theme::where('status', 1)
                       ->orderBy('name', 'asc')
                       ->get();

        $currentTheme = Theme::find($user->profile->theme_id);

        $data = [
            'user'          => $user,
            'themes'        => $themes,
            'currentTheme'  => $currentTheme

        ];

        return view('profiles.edit')->with($data);

    }

    /**
     * оновити профіль юзера
     * FormValidationException
     */
    public function update($username, Request $request)
    {
        $user = $this->getUserByUsername($username);

        $input = Input::only('theme_id', 'location', 'bio', 'twitter_username', 'github_username', 'avatar_status');

        $ipAddress = new CaptureIpTrait;

        $profile_validator = $this->profile_validator($request->all());

        if ($profile_validator->fails()) {

            $this->throwValidationException(
                $request, $profile_validator
            );

            return redirect('profile/'.$user->name.'/edit')->withErrors(validator)->withInput();///////
        }

        if ($user->profile == null) {

            $profile = new Profile;
            $profile->fill($input);
            $user->profile()->save($profile);

        } else {

            $user->profile->fill($input)->save();

        }

        $user->updated_ip_address = $ipAddress->getClientIp();

        $user->save();

        return redirect('profile/'.$user->name.'/edit')->with('success', trans('profile.updateSuccess'));

    }

    public function validator(array $data)
    {
        return Validator::make($data, [
            'name'              => 'required|max:255',
        ]);
    }

    /**
     *оновити дані юзера
     *
     */
    public function updateUserAccount(Request $request, $id)
    {

        $currentUser = \Auth::user();
        $user        = User::findOrFail($id);
        $emailCheck  = ($request->input('email') != '') && ($request->input('email') != $user->email);
        $ipAddress   = new CaptureIpTrait;

        $validator = Validator::make($request->all(), [
            'name'      => 'required|max:255',
        ]);

        $rules = [];

        if ($emailCheck) {
            $rules = [
                'email'     => 'email|max:255|unique:users'
            ];
        }

        $validator = $this->validator($request->all(), $rules);

        if ($validator->fails()) {
            $this->throwValidationException(
                $request, $validator
            );
        }

        $user->name         = $request->input('name');
        $user->first_name   = $request->input('first_name');
        $user->last_name    = $request->input('last_name');

        if ($emailCheck) {
            $user->email = $request->input('email');
        }

        $user->updated_ip_address = $ipAddress->getClientIp();

        $user->save();

        return redirect('profile/'.$user->name.'/edit')->with('success', trans('profile.updateAccountSuccess'));

    }


    /**
     * оновити пароль
     *
     */
    public function updateUserPassword(Request $request, $id)
    {

        $currentUser = \Auth::user();
        $user        = User::findOrFail($id);
        $ipAddress   = new CaptureIpTrait;

        $validator = Validator::make($request->all(),
            [
                'password'              => 'required|min:6|max:20|confirmed',
                'password_confirmation' => 'required|same:password',
            ],
            [
                'password.required'     => trans('auth.passwordRequired'),
                'password.min'          => trans('auth.PasswordMin'),
                'password.max'          => trans('auth.PasswordMax'),
            ]
        );

        if ($validator->fails()) {
            $this->throwValidationException(
                $request, $validator
            );
        }

        if ($request->input('password') != null) {
            $user->password = bcrypt($request->input('password'));
        }

        $user->updated_ip_address = $ipAddress->getClientIp();

        $user->save();

        return redirect('profile/'.$user->name.'/edit')->with('success', trans('profile.updatePWSuccess'));

    }

    /**
     * загрузити і оновити аватар
     *
     */
    public function upload() {
        if(Input::hasFile('file')) {

          $currentUser  = \Auth::user();
          $avatar       = Input::file('file');
          $filename     = 'avatar.' . $avatar->getClientOriginalExtension();
          $save_path    = storage_path() . '/users/id/' . $currentUser->id . '/uploads/images/avatar/';
          $path         = $save_path . $filename;
          $public_path  = '/images/profile/' . $currentUser->id . '/avatar/' . $filename;

         //створ папку
          File::makeDirectory($save_path, $mode = 0755, true, true);

          // зберегти файл
          Image::make($avatar)->resize(300, 300)->save($save_path . $filename);

            // зберегти шлях до файлу
            $currentUser->profile->avatar = $public_path;
            $currentUser->profile->save();

          return response()->json(array('path'=> $path), 200);

        } else {

          return response()->json(false, 200);

        }
    }

    /**
     * показати аватар
     */
    public function userProfileAvatar($id, $image)
    {
        return Image::make(storage_path() . '/users/id/' . $id . '/uploads/images/avatar/' . $image)->response();
    }

    /**
     * видалення профілю
     */
    public function deleteUserAccount(Request $request, $id)
    {

        $currentUser = \Auth::user();
        $user        = User::findOrFail($id);
        $ipAddress   = new CaptureIpTrait;

        $validator = Validator::make($request->all(),
            [
                'checkConfirmDelete'            => 'required',
            ],
            [
                'checkConfirmDelete.required'   => trans('profile.confirmDeleteRequired'),
            ]
        );

        if ($validator->fails()) {
            $this->throwValidationException(
                $request, $validator
            );
        }

        if ($user->id != $currentUser->id) {

            return redirect('profile/'.$user->name.'/edit')->with('error', trans('profile.errorDeleteNotYour'));

        }

        // створити та зашифрувати token
        $sepKey       = $this->getSeperationKey();
        $userIdKey    = $this->getIdMultiKey();
        $restoreKey   = config('settings.restoreKey');
        $encrypter    = config('settings.restoreUserEncType');
        $level1       = $user->id * $userIdKey;
        $level2       = urlencode(Uuid::generate(4) . $sepKey . $level1);
        $level3       = base64_encode($level2);
        $level4       = openssl_encrypt($level3, $encrypter, $restoreKey);
        $level5       = base64_encode($level4);

        // зберегти відновлений токен і ір
        $user->token  = $level5;
        $user->deleted_ip_address = $ipAddress->getClientIp();
        $user->save();

        $this->sendGoodbyEmail($user, $user->token);

        // видалити юзера
        $user->delete();

        $request->session()->flush();
        $request->session()->regenerate();

        return redirect('/login/')->with('success', trans('profile.successUserAccountDeleted'));

    }

    public static function sendGoodbyEmail(User $user, $token) {
        $user->notify(new SendGoodbyeEmail($token));
    }

    public function getIdMultiKey() {
        return $this->idMultiKey;
    }

    public function getSeperationKey() {
        return $this->seperationKey;
    }

}
