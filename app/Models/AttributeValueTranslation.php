<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeValueTranslation extends Model
{
    use HasFactory;

    protected $table = 'attribute_values_translation';

    protected $fillable = [
        'attribute_value_id',
        'name',
        'lang',
    ];

    public function attributeValue()
    {
        return $this->belongsTo(AttributeValue::class);
    }
}
