<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StateTranslation extends Model
{
    use HasFactory;

    protected $table = 'states_translations';

    protected $fillable = [
        'states_id',
        'name',
        'lang',
    ];

    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
