<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    public function attribute() {
        return $this->belongsTo(Attribute::class);
    }

    public function get_translation($field = '', $lang = false) {
        $lang = $lang == false ? app()->getLocale() : $lang;
        $attribute_value_translation = $this->attribute_value_translations->where('lang', $lang)->first();
        return $attribute_value_translation != null ? $attribute_value_translation->$field : $this->$field;
    }

    public function attribute_value_translations() {
        return $this->hasMany(AttributeValueTranslation::class);
    }
}
