<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogCategoryTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['category_name', 'lang', 'blog_category_id', 'slug'];

    public function blogCategory(){
        return $this->belongsTo(BlogCategory::class);
    }

    
}
