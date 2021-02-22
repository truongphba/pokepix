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
}
