<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HeaderLink extends Model
{
    use SoftDeletes;

    protected $fillable = ['url', 'slug'];

    // Always eager load translations
    protected $with = ['translations'];

    // Relationship to translations
    public function translations()
    {
        return $this->hasMany(HeaderLinkTranslation::class);
    }

    /**
     * Helper function to get a translated field if it exists,
     * otherwise return the default (from the main table).
     *
     * @param string $field
     * @param string|bool $lang
     * @return string|null
     */
    public function getTranslation($field = '', $lang = false)
    {
        // If no language given, use the current App locale
        $lang = $lang ? $lang : app()->getLocale();

        $translation = $this->translations
                            ->where('lang', $lang)
                            ->first();

        if ($translation && $translation->$field != null) {
            return $translation->$field;
        }

        // Fallback to the original field on the main table if no translation
        return $this->$field;
    }
}
