<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeaderLinkTranslation extends Model
{
    protected $fillable = ['header_link_id', 'url', 'lang', 'slug'];

    public function headerLink()
    {
        return $this->belongsTo(HeaderLink::class);
    }
}
