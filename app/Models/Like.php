<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'likes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'image_id', 'user_id', 'user'
    ];


    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function image()
    {
        return $this->hasOne('App\Models\Image', 'id', 'image_id');
    }
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    // ];
}
