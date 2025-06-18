<?php

namespace App\Models;

use App;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryAddon extends Model
{
    use HasFactory;

    protected $table = 'category_add_on';

    //id	name	created_at	updated_at	
    protected $fillable = [
        'name',
        'price'
    ];

    public function productAddOns()
    {
        return $this->hasMany(ProductAddOn::class);
    }

    public function getTranslation($field = '', $lang = false){
        $lang = $lang == false ? \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getCurrentLocale() : $lang;
        $category_translation = $this->category_addon_translations->where('lang', $lang)->first();
        return $category_translation != null ? $category_translation->$field : $this->$field;
    }

    public function category_addon_translations(){
    	return $this->hasMany(CategoryAddonTranslation::class);
    }

}
