<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\TrixField.
 *
 * @property int                             $id
 * @property string                          $type
 * @property string                          $language
 * @property string                          $body
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\TrixField careAmbassador($language)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\TrixField newModelQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\TrixField newQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\TrixField query()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\TrixField whereBody($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\TrixField whereCreatedAt($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\TrixField whereId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\TrixField whereLanguage($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\TrixField whereType($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\TrixField whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TrixField extends Model
{
    /**
     * The Type to get scripts for the Care Ambassadors Page.
     */
    const CARE_AMBASSADOR_SCRIPT                  = 'care_ambassador_script';
    const CARE_AMBASSADOR_UNREACHABLE_USER_SCRIPT = 'care_ambassador_unreachable_user_script';

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
     * @param mixed $enrollableIsUnreachableUser
     */
    public function scopeCareAmbassador($builder, $language, $enrollableIsUnreachableUser = false)
    {
        $scriptLanguage = '';

        if (stringMeansEnglish($language)) {
            $scriptLanguage = self::ENGLISH_LANGUAGE;
        }

        if (stringMeansSpanish($language)) {
            $scriptLanguage = self::SPANISH_LANGUAGE;
        }

        $type = $enrollableIsUnreachableUser ? self::CARE_AMBASSADOR_UNREACHABLE_USER_SCRIPT : self::CARE_AMBASSADOR_SCRIPT;

        $builder->where('type', $type)
            ->where(function ($q) use ($scriptLanguage) {
                $q->where('language', $scriptLanguage);
            });
    }
}
