<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'name', 'file', 'note', 'content', 'likes_count', 'comments_count'
    ];

    public function getFileAttribute($file)
    {
        return url($file);
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function likes()
    {
        return $this->hasMany('App\Models\Like', 'image_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment', 'image_id', 'id');
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
