<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandTranslation extends Model
{
  protected $fillable = ['name', 'lang', 'brand_id', 'meta_title', 'meta_description'];

  public function brand(){
    return $this->belongsTo(Brand::class);
  }
}
