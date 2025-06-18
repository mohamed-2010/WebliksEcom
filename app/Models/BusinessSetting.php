<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    protected $fillable = ['type', 'value', 'created_at', 'updated_at'];
    protected $table = 'business_settings';
    // public $timestamps = false;
    // public $incrementing = false;
    // protected $primaryKey = 'type';
    // protected $keyType = 'string';
}
