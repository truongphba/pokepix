<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    public function getAvatarUrl()
    {
        if ($this->avatar) {
            return url($this->avatar);
        }
        return '';
    }

    public function getCoverUrl()
    {
        if ($this->cover) {
            return url($this->cover);
        }
        return '';
    }
}
