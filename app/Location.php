<?php
namespace App;

use Franzose\ClosureTable\Models\Entity;

class Location extends Entity implements LocationInterface
{
    /**
     * The table associated with the model.
     *
     * @var string

     */
    protected $table = 'locations';

    /**
     * ClosureTable model instance.
     *
     * @var locationClosure
     */
    protected $closure = 'App\LocationClosure';

    /**
     * Mass assignable attributes
     *
     * @var array
     */
    protected $fillable = [ 'name', 'phone', 'address_line_1', 'address_line_2', 'city', 'postal_code', 'billing_code', 'location_code' ];

    public static function getNonRootLocations($parent_location_id = false)
    {
        if($parent_location_id) {
            // get parent_id from $parent_location_code
            $parent_location = Location::where('id', '=', $parent_location_id)->first();
            if(!$parent_location) {
                return false;
            }
            return Location::where('parent_id', '=', $parent_location->id)->lists('name', 'id');
        } else {
            return Location::where('parent_id', '!=', 'NULL')->lists('name', 'id');
        }
    }

    public static function getLocationName($id)
    {
        $q =  Location::where('id', '=', $id)->select('name')->first();
        return $q['name'];
    }

    public static function getAllNodes()
    {
        return Location::all()->lists('name', 'id');
    }

    public static function getAllParents()
    {
        return Location::whereRaw('position = 0 AND real_depth = 0')->lists('name', 'id');
    }

    public static function getParentsSubs($id)
    {
        return Location::where('parent_id', '=', $id)->lists('name', 'id');
    }

}
