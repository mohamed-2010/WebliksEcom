<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAddOnTranslation extends Model
{
    use HasFactory;

    protected $table = 'product_add_on_translations';

    protected $fillable = ['name', 'lang', 'product_add_on_id'];

    public function product_addon(){
    	return $this->belongsTo(ProductAddOn::class);
    }
}
