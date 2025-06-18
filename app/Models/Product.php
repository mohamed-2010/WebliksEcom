<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class Product extends Model
{

    protected $guarded = ['choice_attributes'];

    protected $with = ['product_translations', 'taxes'];

    public function getTranslation($field = '', $lang = false, $fallback = true)
    {
        // Default to the current locale if no language is provided
        $lang = $lang == false ? \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getCurrentLocale() : $lang;

        // Get the translation for the specified language
        $product_translations = $this->product_translations->where('lang', $lang)->first();

        // If translation is found, return it; otherwise, return the fallback value
        if ($product_translations != null) {
            return $product_translations->$field;
        }

        // If fallback is disabled, return null or an empty string
        if ($fallback) {
            return $this->$field; // Fallback to the default field value (usually in English)
        }

        return null; // Return null if no translation is found and fallback is disabled
    }


    public function product_translations()
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('status', 1);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function taxes()
    {
        return $this->hasMany(ProductTax::class);
    }

    public function flash_deal_product()
    {
        return $this->hasOne(FlashDealProduct::class);
    }

    public function bids()
    {
        return $this->hasMany(AuctionProductBid::class);
    }

    public function scopePhysical($query)
    {
        return $query->where('digital', 0);
    }

    public function scopeDigital($query)
    {
        return $query->where('digital', 1);
    }

    public function scopeActive($query)
    {
        return $query->where('published', 1);
    }
}
