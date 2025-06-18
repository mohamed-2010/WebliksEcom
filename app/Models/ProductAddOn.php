<?php

namespace App\Models;

use App;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAddOn extends Model
{
    use HasFactory;

    protected $table = 'product_add_ons';

    //id	name	price	category_add_on_id	created_at	updated_at	
    protected $fillable = [
        'name',
        'price',
        'category_add_on_id',
    ];

    public function categoryAddOn()
    {
        return $this->belongsTo(CategoryAddon::class);
    }

    public function getTranslation($field = '', $lang = false){
        $lang = $lang == false ? \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getCurrentLocale() : $lang;
        $category_translation = $this->product_addon_translations->where('lang', $lang)->first();
        return $category_translation != null ? $category_translation->$field : $this->$field;
    }

    public function product_addon_translations(){
    	return $this->hasMany(ProductAddOnTranslation::class);
    }

}
