<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CircleLinkHealth\Customer\Entities\ProviderInfo.
 *
 * @property int                                      $id
 * @property int|null                                 $is_clinical
 * @property int                                      $user_id
 * @property string|null                              $prefix
 * @property string|null                              $npi_number
 * @property string|null                              $specialty
 * @property string                                   $created_at
 * @property string                                   $updated_at
 * @property string|null                              $deleted_at
 * @property mixed                                    $address
 * @property mixed                                    $city
 * @property mixed                                    $first_name
 * @property mixed                                    $last_name
 * @property mixed                                    $state
 * @property mixed                                    $zip
 * @property \CircleLinkHealth\Customer\Entities\User $user
 *
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\ProviderInfo onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderInfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderInfo whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderInfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderInfo whereIsClinical($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderInfo whereNpiNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderInfo wherePrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderInfo whereSpecialty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderInfo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderInfo whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ProviderInfo withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\ProviderInfo withoutTrashed()
 * @mixin \Eloquent
 *
 * @property int                                                                                         $approve_own_care_plans
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ProviderInfo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ProviderInfo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ProviderInfo query()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ProviderInfo whereApproveOwnCarePlans($value)
 *
 * @property string|null $sex
 * @property string|null $pronunciation
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ProviderInfo wherePronunciation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ProviderInfo whereSex($value)
 *
 * @property int|null                    $revision_history_count
 * @property \App\ProviderSignature|null $signature
 */
class ProviderInfo extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'is_clinical',
        'user_id',
        'prefix',
        'npi_number',
        'specialty',
        'approve_own_care_plans',
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
    protected $table = 'provider_info';

    public function getAddressAttribute()
    {
        return $this->user->address;
    }

    public function getCityAttribute()
    {
        return $this->user->city;
    }

    public function getFirstNameAttribute()
    {
        return $this->user->getFirstName();
    }

    public function getLastNameAttribute()
    {
        return $this->user->getLastName();
    }

    public function getStateAttribute()
    {
        return $this->user->state;
    }

    public function getZipAttribute()
    {
        return $this->user->zip;
    }

    public function setAddressAttribute($value)
    {
        $this->user->address = $value;
        $this->user->save();

        return true;
    }

    public function setCityAttribute($value)
    {
        $this->user->city = $value;
        $this->user->save();

        return true;
    }

    public function setFirstNameAttribute($value)
    {
        $this->user->setFirstName($value);
        $this->user->save();

        return true;
    }

    public function setLastNameAttribute($value)
    {
        $this->user->setLastName($value);
        $this->user->save();

        return true;
    }

    public function setStateAttribute($value)
    {
        $this->user->state = $value;
        $this->user->save();

        return true;
    }

    public function setZipAttribute($value)
    {
        $this->user->zip = $value;
        $this->user->save();

        return true;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
