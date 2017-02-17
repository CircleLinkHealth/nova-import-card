<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use SoftDeletes;

    //Aprima's constant location id.
    const UPG_PARENT_LOCATION_ID = 26;

    /**
     * Mass assignable attributes
     *
     * @var array
     */
    protected $fillable = [
        'practice_id',
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

    public function clinicalEmergencyContact()
    {
        return $this->morphToMany(User::class, 'contactable', 'contacts')
            ->withPivot('name')
            ->wherePivot('name', '=', 'in_addition_to_billing_provider')
            ->orWherePivot('name', '=', 'instead_of_billing_provider')
            ->withTimestamps();
    }

    public function practice()
    {
        return $this->belongsTo(Practice::class);
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

    public function getEmrDirectAddressAttribute()
    {
        return $this->emrDirect->first()->address ?? null;
    }

    public function setEmrDirectAddressAttribute($address)
    {
        $this->emrDirect()->delete();

        if (empty($address)) {
            //assume we wanted to delete the previous address
            return true;
        }

        $this->emrDirect()->create([
            'address' => $address,
        ]);
    }

    public function emrDirect()
    {
        return $this->morphMany(EmrDirectAddress::class, 'emrDirectable');
    }
}
