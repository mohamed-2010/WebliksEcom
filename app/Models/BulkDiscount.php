<?php

namespace App\Models;

use App;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkDiscount extends Model
{
    use HasFactory;

    protected $table = 'bulk_discount';
    protected $fillable = [
        'category_ids',
        'brand_ids',
        'date_start',
        'date_end',
        'discount',
        'discount_type',
    ];

}
