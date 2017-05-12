<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activation extends Model
{

    protected $table = 'activations';

    protected $guarded = [
    	'id'
    ];

    protected $hidden = [
        'user_id',
        'token',
        'ip_address'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}