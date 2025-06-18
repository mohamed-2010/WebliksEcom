<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branche extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function cities()
    {
        return $this->belongsToMany(City::class, 'branche_cities');
    }

    public function prancheCities()
    {
        return $this->hasMany(BrancheCities::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'branche_user');
    }
}
