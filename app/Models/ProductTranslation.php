<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTranslation extends Model
{
    protected $fillable = ['product_id', 'name', 'unit', 'description', 'lang', 'meta_title', 'meta_description', 'slug'];

    public function product(){
      return $this->belongsTo(Product::class);
    }
}
