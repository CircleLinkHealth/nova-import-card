<?php
namespace App;

use Franzose\ClosureTable\Models\Entity;

class Location extends Entity implements LocationInterface
{
    //Aprima's constant location ID.
    const APRIMA_ID = 26;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lv_locations';

    /**
     * Mass assignable attributes
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'billing_code',
        'location_code',
        'position',
    ];


    public function program()
    {
        return $this->belongsTo(Program::class, 'location_id');
    }

    public function user()
    {
        return $this->belongsToMany(User::class);
    }

    public static function getLocationsForBlog($blogId)
    {
        $q = Location::where('program_id', '=', $blogId)->get();

        return ($q == null) ? '' : $q;
    }


    public static function getNonRootLocations($parent_location_id = false)
    {
        if ($parent_location_id) {
            // get parent_id from $parent_location_code
            $parent_location = Location::where('id', '=', $parent_location_id)->first();
            if (!$parent_location) {
                return false;
            }
            return Location::where('parent_id', '=', $parent_location->id)->lists('name', 'id')->all();
        } else {
            return Location::where('parent_id', '!=', 'NULL')->lists('name', 'id')->all();
        }
    }

    public static function getLocationName($id)
    {
        $q = Location::where('id', '=', $id)->select('name')->first();
        return $q['name'];
    }

    public static function getAllNodes()
    {
        return Location::all()->lists('name', 'id')->all();
    }

    public static function getAllParents()
    {
        return Location::whereRaw('parent_id IS NULL')->lists('name', 'id')->all();
    }

    public static function getParents($id)
    {
        $l = Location::find($id);
        return Location::where('id', $l->parent_id)->lists('name', 'id')->all();
    }

    public static function getParentsSubs($id)
    {
        return Location::where('parent_id', '=', $id)->lists('name', 'id')->all();
    }

}
