<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use CircleLinkHealth\Core\StringManipulation;

/**
 * CircleLinkHealth\Customer\Entities\PhoneNumber.
 *
 * @property int                                      $id
 * @property int                                      $user_id
 * @property int                                      $location_id
 * @property string|null                              $number
 * @property string|null                              $extension
 * @property string|null                              $type
 * @property int                                      $is_primary
 * @property string                                   $created_at
 * @property string                                   $updated_at
 * @property string|null                              $deleted_at
 * @property \CircleLinkHealth\Customer\Entities\User $user
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereCreatedAt($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereDeletedAt($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereExtension($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereId($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereIsPrimary($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereLocationId($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereNumber($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereType($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereUpdatedAt($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereUserId($value)
 * @mixin \Eloquent
 * @property string                                                                                      $number_with_dashes
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PhoneNumber newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PhoneNumber newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PhoneNumber query()
 * @property int|null                                                                                    $revision_history_count
 */
class PhoneNumber extends \CircleLinkHealth\Core\Entities\BaseModel
{
    //types
    const HOME   = 'home';
    const MOBILE = 'mobile';
    const ALTERNATE   = 'alternate';

    public $phi = [
        'number',
        'extension',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'location_id',
        'number',
        'type',
        'is_primary',
        'extension',
    ];
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'phone_numbers';

    /**
     * Get phone number in this format xxx-xxx-xxxx.
     *
     * @return string
     */
    public function getNumberWithDashesAttribute()
    {
        return (new StringManipulation())->formatPhoneNumber($this->number);
    }

    public static function getTypes(): array
    {
        return [
            1 => PhoneNumber::HOME,
            2 => PhoneNumber::MOBILE,
            3 => PhoneNumber::WORK,
        ];
    }

    /**
     * Set the phone number.
     *
     * @param $value
     */
    public function setNumberAttribute($value)
    {
        $this->attributes['number'] = isProductionEnv() ? (new StringManipulation())->formatPhoneNumberE164($value) : $value;
    }

    public function user()
    {
        return $this->belongsTo('CircleLinkHealth\Customer\Entities\User', 'id', 'user_id');
    }
}
