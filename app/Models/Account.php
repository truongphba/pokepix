<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Account extends Authenticatable
{
    use Notifiable;
    protected $table = 'accounts';

    protected $fillable = [
        'username','password'
    ];
    protected $hidden = [
        'password'
    ];
    protected $primaryKey = 'id';

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }
}
