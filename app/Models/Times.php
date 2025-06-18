<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Times extends Model
{
    use HasFactory;
    protected $fillable = ['friday', 'saturday','sunday','monday','tuesday','wednesday','thursday'];
    protected $casts = [
        'friday' => 'array',
        'saturday' => 'array',
        'sunday' => 'array',
        'monday' => 'array',
        'tuesday' => 'array',
        'wednesday' => 'array',
        'thursday' => 'array'
    ];
}
