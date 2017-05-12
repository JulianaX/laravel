<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model {

    protected $table = 'profiles';

    protected $guarded = [
    	'id'
    ];

	/**
	 * поля для профілю
	 */
	protected $fillable = [
		'theme_id',
        'location',
		'bio',
		'twitter_username',
		'github_username',
        'user_profile_bg',
        'avatar',
        'avatar_status',
	];

    protected $casts = [
        'theme_id' => 'integer',
    ];

	/**
	 * profile belongs to a user
	 */
	public function user()
	{
		return $this->belongsTo('App\Models\User');
	}

    /**
     * Profile hasOne Theme
     */
    public function theme()
    {
        return $this->hasOne('App\Models\Theme');
    }


}