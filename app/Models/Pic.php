<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pic extends Model
{
    protected $table = 'pics';

    public function getFileUrl()
    {
        return url($this->file);
    }

    public function categories(){
        return $this->belongsToMany('App\Models\Category', 'category_pic', 'pic_id', 'category_id')->withPivot('type');
    }
}
