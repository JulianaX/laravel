<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use dbmigration\LaravelRoles\Traits\HasRoleAndPermission;

class User extends Authenticatable
{
    use HasRoleAndPermission;
    use Notifiable;
    use SoftDeletes;

    protected $table = 'users';

    protected $guarded = ['id']; //захищений id

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'activated',
        'token',
        'signup_ip_address',
        'signup_confirmation_ip_address',
        'signup_sm_ip_address',
        'admin_ip_address',
        'updated_ip_address',
        'deleted_ip_address',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'activated',
        'token',
    ];

    protected $dates = [
        'deleted_at'
    ];

    public function social()
    {
        return $this->hasMany('App\Models\Social');
    }

    /**
     * зв'язок з профілем
     */
    public function profile()
    {
        return $this->hasOne('App\Models\Profile');
    }

    public function profiles()
    {
        //в таблиці оновлені дати
        return $this->belongsToMany('App\Models\Profile')->withTimestamps();
    }

    public function hasProfile($name)
    {
        foreach($this->profiles as $profile)
        {
            if($profile->name == $name) return true;
        }

        return false;
    }

    public function assignProfile($profile)
    {
        return $this->profiles()->attach($profile);
    }

    public function removeProfile($profile)
    {
        return $this->profiles()->detach($profile);
    }

}
