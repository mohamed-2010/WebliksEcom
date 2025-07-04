<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryTranslation extends Model
{
    protected $fillable = ['name', 'lang', 'category_id', 'meta_title', 'meta_description'];

    public function category(){
    	return $this->belongsTo(Category::class);
    }
}
