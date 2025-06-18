<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeNavbarTrans extends Model
{
    use HasFactory;
    protected $table='home_navbar_translations';
    protected $fillable = ['label', 'link', 'lang'];
}

