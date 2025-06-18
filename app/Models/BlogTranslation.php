<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'short_description', 'lang', 'blog_id', 'slug', 'description', 'meta_title', 'meta_img', 'meta_description', 'meta_keywords'];

    public function blog(){
        return $this->belongsTo(Blog::class);
    }
}
