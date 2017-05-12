<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\Theme;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Validator;

class ThemesManagementController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * всі теми
     *
     */
    public function index()
    {

        $users = User::all();

        $themes = Theme::orderBy('name', 'asc')->get();

        return View('themesmanagement.show-themes', compact('themes', 'users'));
    }

    /**
     * форма для створення теми
     *
     */
    public function create()
    {
        return view('themesmanagement.add-theme');
    }

    /**
     * збереження теми
     *
     */
    public function store(Request $request)
    {

        $input = Input::only('name', 'link', 'notes', 'status');

        $validator = Validator::make($input, Theme::rules());

        if ($validator->fails()) {

            $this->throwValidationException(
                $request, $validator
            );

            return redirect('themes/create')->withErrors($validator)->withInput();
        }

        $theme = Theme::create([
            'name'          => $request->input('name'),
            'link'          => $request->input('link'),
            'notes'         => $request->input('notes'),
            'status'        => $request->input('status'),
            'taggable_id'   => 0,
            'taggable_type' => 'theme'
        ]);

        $theme->taggable_id = $theme->id;
        $theme->save();

        return redirect('themes/'.$theme->id)->with('success', trans('themes.createSuccess'));

    }

    /**
     * подивитися тему
     *
     */
    public function show($id)
    {
        $theme = Theme::find($id);
        $users = User::all();
        $themeUsers = [];

        foreach ($users as $user) {
            if ($user->profile->theme_id === $theme->id) {
                $themeUsers[] = $user;
            }
        }

        $data = [
            'theme'        => $theme,
            'themeUsers'   => $themeUsers,
        ];

        return view('themesmanagement.show-theme')->with($data);
    }

    /**
     * форма для редагування теми
     *
     */
    public function edit($id)
    {
        $theme = Theme::find($id);
        $users = User::all();
        $themeUsers = [];

        foreach ($users as $user) {
            if ($user->profile->theme_id === $theme->id) {
                $themeUsers[] = $user;
            }
        }

        $data = [
            'theme'        => $theme,
            'themeUsers'   => $themeUsers,
        ];

        return view('themesmanagement.edit-theme')->with($data);
    }

    /**
     * оновлення теми
     *
     */
    public function update(Request $request, $id)
    {
        $theme = Theme::find($id);

        $input = Input::only('name', 'link', 'notes', 'status');

        $validator = Validator::make($input, Theme::rules($id));

        if ($validator->fails()) {

            $this->throwValidationException(
                $request, $validator
            );

            return redirect('themes/'.$theme->id.'/edit')->withErrors($validator)->withInput();
        }

        $theme->fill($input)->save();

        return redirect('themes/'.$theme->id)->with('success', trans('themes.updateSuccess'));

    }

    /**
     * видалення теми
     *
     */
    public function destroy($id)
    {

        $default = Theme::findOrFail(1);
        $theme = Theme::findOrFail($id);

        if ($theme->id != $default->id) {
            $theme->delete();
            return redirect('themes')->with('success', trans('themes.deleteSuccess'));
        }
        return back()->with('error', trans('themes.deleteSelfError'));

    }
}
