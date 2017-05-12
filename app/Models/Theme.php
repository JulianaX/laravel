<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Theme extends Model
{
    use SoftDeletes;

    protected $table = 'themes';

    protected $guarded = [
    	'id'
    ];

	/**
	 * поля для заповнення
	 */
	protected $fillable = [
		'name',
		'link',
        'notes',
        'status',
		'taggable_id',
		'taggable_type'
	];

    /**
     * дата
     */
    protected $dates = [
        'deleted_at'
    ];

    public static function rules ($id=0, $merge=[]) {
        return array_merge(
            [
                'name'   => 'required|min:3|max:50|unique:themes,name' . ($id ? ",$id" : ''),
                'link'   => 'required|min:3|max:255|unique:themes,link' . ($id ? ",$id" : ''),
                'notes'  => 'max:500',
                'status' => 'required'
            ],
            $merge);
    }

    /**
     * Theme hasMany Profile
     */
    public function profile()
    {
        return $this->hasMany('App\Models\Profile');
    }

}
