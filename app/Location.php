<?php
namespace App;

use App\Traits\HasEmrDirectAddress;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

/**
 * App\Location
 *
 * @property int $id
 * @property int $practice_id
 * @property int $is_primary
 * @property string|null $external_department_id
 * @property string $name
 * @property string $phone
 * @property string|null $fax
 * @property string $address_line_1
 * @property string|null $address_line_2
 * @property string $city
 * @property string $state
 * @property string|null $timezone
 * @property string $postal_code
 * @property string|null $ehr_login
 * @property string|null $ehr_password
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $clinicalEmergencyContact
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\EmrDirectAddress[] $emrDirect
 * @property mixed $emr_direct_address
 * @property-read \App\Location $parent
 * @property-read \App\Practice $practice
 * @property-read \App\Practice $program
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $providers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Location onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereAddressLine1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereAddressLine2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereEhrLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereEhrPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereExternalDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereFax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location wherePracticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Location withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Location withoutTrashed()
 * @mixin \Eloquent
 */
class Location extends \App\BaseModel
{
    use HasEmrDirectAddress,
        Notifiable,
        SoftDeletes;

    //Aprima's constant location id.
    const UPG_PARENT_LOCATION_ID = 26;

    /**
     * Mass assignable attributes
     *
     * @var array
     */
    protected $fillable = [
        'practice_id',
        'is_primary',
        'name',
        'phone',
        'fax',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'timezone',
        'postal_code',
        'ehr_login',
        'ehr_password',
    ];

    public static function getLocationsForBlog($blogId)
    {
        $q = Location::where('program_id', '=', $blogId)->get();

        return ($q == null)
            ? ''
            : $q;
    }

    public static function getNonRootLocations($parent_location_id = false)
    {
        if ($parent_location_id) {
            $parent_location = Location::where('id', '=', $parent_location_id)->first();
            if (!$parent_location) {
                return false;
            }

            return Location::where('parent_id', '=', $parent_location->id)->pluck('name', 'id')->all();
        } else {
            return Location::where('parent_id', '!=', 'NULL')->pluck('name', 'id')->all();
        }
    }

    public static function getLocationName($id)
    {
        $q = Location::where('id', '=', $id)->select('name')->first();

        return $q['name'];
    }

    public static function getAllNodes()
    {
        return Location::all()->pluck('name', 'id')->all();
    }

    public static function getAllParents()
    {
        return Location::whereRaw('parent_id IS NULL')->pluck('name', 'id')->all();
    }

    public static function getParents($id)
    {
        $l = Location::find($id);

        return Location::where('id', $l->parent_id)->pluck('name', 'id')->all();
    }

    public static function getParentsSubs($id)
    {
        return Location::where('parent_id', '=', $id)->pluck('name', 'id')->all();
    }

    public static function setPrimary(Location $loc)
    {

        //set all other practices to 0
        $toRemovePrimaryFrom = $loc->practice->locations()->where('is_primary', 1)->get();

        foreach ($toRemovePrimaryFrom as $location) {
            $location->is_primary = 0;
            $location->save();
        }

        $loc->is_primary = 1;
        $loc->save();

        return $loc;
    }

    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }

    public function clinicalEmergencyContact()
    {
        return $this->morphToMany(User::class, 'contactable', 'contacts')
            ->withPivot('name')
            ->withTimestamps();
    }

    public function program()
    {
        return $this->belongsTo(Practice::class, 'location_id');
    }

    public function parent()
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function providers()
    {
        return $this->belongsToMany(User::class)
            ->ofType('provider');
    }

    public function user()
    {
        return $this->belongsToMany(User::class);
    }
}
