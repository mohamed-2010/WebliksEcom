<?php

namespace App\Models;

use App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
    use SoftDeletes;

    protected $with = ['blog_translations'];

    public function getTranslation($field = '', $lang = false){
      $lang = $lang == false ? \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getCurrentLocale() : $lang;
      $attribute_translation = $this->blog_translations->where('lang', $lang)->first();
      return $attribute_translation != null ? $attribute_translation->$field : $this->$field;
    }

    public function blog_translations(){
      return $this->hasMany(BlogTranslation::class);
    }
    
    public function category() {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

}
