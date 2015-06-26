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

    public static function getNonRootLocations()
    {
        return Location::where('parent_id', '!=', 'NULL')->lists('name', 'id');
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
