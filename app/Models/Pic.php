<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pic extends Model
{
    protected $table = 'pics';

    public function getFileUrl()
    {
        if($this->file) return url($this->file);
        return null;
    }

    public function getSvgImageUrl()
    {
        if($this->svgImageURL) return url($this->svgImageURL);
        return null;
    }

    public function getOutlineImageUrl()
    {
        if($this->outlineImageURL) return url($this->outlineImageURL);
        return null;
    }

    public function getOriginalImageUrl()
    {
        if($this->outlineImageURL) return url($this->originalImageURL);
        return null;
    }

    public function getColorImageUrl()
    {
        if($this->outlineImageURL) return url($this->colorImageURL);
        return null;
    }

    public function categories(){
        return $this->belongsToMany('App\Models\Category', 'category_pic', 'pic_id', 'category_id')->withPivot('type');
    }
}
