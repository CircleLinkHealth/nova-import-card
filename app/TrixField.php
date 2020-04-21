<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * App\TrixField.
 *
 * @property int                             $id
 * @property string                          $type
 * @property string                          $language
 * @property string                          $body
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TrixField careAmbassador($language)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TrixField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TrixField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TrixField query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TrixField whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TrixField whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TrixField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TrixField whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TrixField whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TrixField whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TrixField extends Model
{
    /**
     * The Type to get scripts for the Care Ambassadors Page.
     */
    const CARE_AMBASSADOR_SCRIPT = 'care_ambassador_script';

    const ENGLISH_LANGUAGE = 'en';
    const SPANISH_LANGUAGE = 'es';

    protected $fillable = [
        'type',
        'language',
        'body',
    ];

    /**
     * Get Care.
     *
     * @param $builder
     * @param $language
     */
    public function scopeCareAmbassador($builder, $language)
    {
        $scriptLanguage = '';

        if (in_array(strtolower($language), [
            'en',
            'eng',
            'english',
        ]) ||
            Str::startsWith(strtolower($language), 'en')
        ) {
            $scriptLanguage = self::ENGLISH_LANGUAGE;
        }

        if (in_array(strtolower($language), [
            'sp',
            'es',
            'spanish',
            'spa',
        ]) ||
            Str::startsWith(strtolower($language), ['es', 'sp'])
        ) {
            $scriptLanguage = self::SPANISH_LANGUAGE;
        }

        $builder->where('type', TrixField::CARE_AMBASSADOR_SCRIPT)
            ->where(function ($q) use ($scriptLanguage) {
                $q->where('language', $scriptLanguage)
                        //Default to english language. We don't want cases where enrollee has something unexpected in language field,
                        // and we do not bring any script because of that
                    ->orWhere('language', self::ENGLISH_LANGUAGE);
            });
    }
}
