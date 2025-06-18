<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrancheCities extends Model
{
    use HasFactory;

    protected $fillable = [
        'branche_id',
        'city_id'
    ];

    public function pranche()
    {
        return $this->belongsTo(Branche::class, 'branche_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
