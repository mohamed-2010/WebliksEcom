<?php

namespace App\Models;

use App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogCategory extends Model
{
    use SoftDeletes;
    
    public function posts()
    {
        return $this->hasMany(Blog::class);
    }

    protected $with = ['blog_category_translations'];

    public function getTranslation($field = '', $lang = false){
      $lang = $lang == false ? \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getCurrentLocale() : $lang;
      $blog_category_translations = $this->blog_category_translations->where('lang', $lang)->first();
      return $blog_category_translations != null ? $blog_category_translations->$field : $this->$field;
    }

    public function blog_category_translations(){
      return $this->hasMany(BlogCategoryTranslation::class);
    }


}
