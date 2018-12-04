<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\ProviderInfo.
 *
 * @property int         $id
 * @property int|null    $is_clinical
 * @property int         $user_id
 * @property string|null $prefix
 * @property string|null $npi_number
 * @property string|null $specialty
 * @property string      $created_at
 * @property string      $updated_at
 * @property string|null $deleted_at
 * @property mixed       $address
 * @property mixed       $city
 * @property mixed       $first_name
 * @property mixed       $last_name
 * @property mixed       $state
 * @property mixed       $zip
 * @property \App\User   $user
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
 */
class ProviderInfo extends \App\BaseModel
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

    // address
    public function getAddressAttribute()
    {
        return $this->user->address;
    }

    // city
    public function getCityAttribute()
    {
        return $this->user->city;
    }

    // END RELATIONSHIPS

    // START ATTRIBUTES

    // first_name
    public function getFirstNameAttribute()
    {
        return $this->user->getFirstName();
    }

    // last_name
    public function getLastNameAttribute()
    {
        return $this->user->getLastName();
    }

    // state
    public function getStateAttribute()
    {
        return $this->user->state;
    }

    // zip
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

    // START RELATIONSHIPS

    // user

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // END ATTRIBUTES
}
