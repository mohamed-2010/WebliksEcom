<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryAddonTranslation extends Model
{
    use HasFactory;

    protected $table = 'category_addon_translations';

    protected $fillable = ['name', 'lang', 'category_addon_id'];

    public function catgory_addon(){
    	return $this->belongsTo(CategoryAddon::class);
    }
}
