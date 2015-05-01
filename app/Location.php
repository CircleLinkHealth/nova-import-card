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
    protected $closure = 'App\locationClosure';

    /**
     * Mass assignable attributes
     *
     * @var array
     */
    protected $fillable = [ 'name', 'phone', 'address_line_1', 'address_line_2', 'city', 'postal_code', 'billing_code', 'location_code' ];

    /*
     * COME BACK HERE LATER
     * This function should return an array of arrays with the location hierarchies
     */
    public static function getLocationHierarchy()
    {
        $roots = Location::getRoots()->sortBy('name');

//        foreach( $roots as $root ) {
//            var_dump( $root->name );
//            //	$tree = Location::getTree()->find( $root->id );
//            if ( $root->hasChildren() ) {
//                var_dump( $root->getChildren() );
//            }
//        }

        return $roots;
    }

}
