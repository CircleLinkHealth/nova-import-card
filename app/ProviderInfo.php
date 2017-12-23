<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\ProviderInfo
 *
 * @property int $id
 * @property int|null $is_clinical
 * @property int $user_id
 * @property string|null $prefix
 * @property string|null $npi_number
 * @property string|null $specialty
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property mixed $address
 * @property mixed $city
 * @property mixed $first_name
 * @property mixed $last_name
 * @property mixed $state
 * @property mixed $zip
 * @property-read \App\User $user
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

    public $timestamps = false;
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'provider_info';
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';
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

    // START RELATIONSHIPS

    // user

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    // END RELATIONSHIPS


    // START ATTRIBUTES

    // first_name
    public function getFirstNameAttribute()
    {
        return $this->user->first_name;
    }

    public function setFirstNameAttribute($value)
    {
        $this->user->first_name = $value;
        $this->user->save();

        return true;
    }

    // last_name
    public function getLastNameAttribute()
    {
        return $this->user->last_name;
    }

    public function setLastNameAttribute($value)
    {
        $this->user->last_name = $value;
        $this->user->save();

        return true;
    }

    // address
    public function getAddressAttribute()
    {
        return $this->user->address;
    }

    public function setAddressAttribute($value)
    {
        $this->user->address = $value;
        $this->user->save();

        return true;
    }

    // city
    public function getCityAttribute()
    {
        return $this->user->city;
    }

    public function setCityAttribute($value)
    {
        $this->user->city = $value;
        $this->user->save();

        return true;
    }

    // state
    public function getStateAttribute()
    {
        return $this->user->state;
    }

    public function setStateAttribute($value)
    {
        $this->user->state = $value;
        $this->user->save();

        return true;
    }

    // zip
    public function getZipAttribute()
    {
        return $this->user->zip;
    }

    public function setZipAttribute($value)
    {
        $this->user->zip = $value;
        $this->user->save();

        return true;
    }

    // END ATTRIBUTES
}
