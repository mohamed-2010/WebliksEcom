<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrancheUser extends Model
{
    use HasFactory;
    protected $table = 'branche_user';

    protected $fillable = [
        'branche_id',
        'user_id',
    ];

    public function branche()
    {
        return $this->belongsTo(Branche::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
