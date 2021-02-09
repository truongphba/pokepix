<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'avatar', 'device_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'device_id',
    ];

    public function likes()
    {
        return $this->hasMany('App\Models\Like', 'user', 'id');
    }

    public function likeds()
    {
        return $this->hasMany('App\Models\Like', 'user_id', 'id');
    }

    public function getAvatarAttribute($avatar)
    {
        if($avatar) return url($avatar);
    }
    public function getAvatarData()
    {
        return parse_url($this->avatar)['path'];
    }

    public function images()
    {
        return $this->hasMany('App\Models\Image', 'user_id', 'id');
    }

    // Người theo dõi
    public function followers()
    {
        return $this->hasMany('App\Models\Follow', 'user_id', 'id');
    }

    // Đang theo dõi
    public function followings()
    {
        return $this->hasMany('App\Models\Follow', 'follower', 'id');
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
