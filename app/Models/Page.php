<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class Page extends Model
{
  public function getTranslation($field = '', $lang = false){
      $lang = $lang == false ? \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getCurrentLocale() : $lang;
      $page_translation = $this->page_translations->where('lang', $lang)->first();
      return $page_translation != null ? $page_translation->$field : $this->$field;
  }

  public function page_translations(){
    return $this->hasMany(PageTranslation::class);
  }
}
