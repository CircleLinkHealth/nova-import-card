<?php

namespace App\CLH\CCD\ImportRoutine;

use App\CLH\CCD\Identifier\IdentificationManager;
use App\CLH\CCD\Vendor\CcdVendor;

class RoutineBuilder
{
    protected $ccd;

    public function __construct($ccd)
    {
        $this->ccd = $ccd;
    }

    public function getRoutine()
    {
        $idManager = new IdentificationManager( $this->ccd );
        $identifiers = $idManager->identify();

        if ( !$identifiers ) return $this->getDefaultRoutine();

        $vendors = CcdVendor::all()->all();

        foreach ( $identifiers as $key => $value ) {
            $vendors = array_filter( $vendors, function ($vendor) use ($key, $value) {
                if ( empty($vendor->$key) ) return true;
                return $vendor->$key == $value;
            } );
            if ( count( $vendors ) == 1 ) break;
        }

        if ( empty($vendors) ) return $this->getDefaultRoutine();

        if ( count( $vendors ) > 1 )
        {
            foreach ( $identifiers as $key => $value ) {
                $vendors = array_filter( $vendors, function ($vendor) use ($key, $value) {
                    /*
                     * If there's more than one vendors, iterate again, but check strictly
                     * by getting rid of this line
                     */
//                    if ( empty($vendor->$key) ) return true;
                    return $vendor->$key == $value;
                } );
                if ( count( $vendors ) == 1 ) break;
            }
        }

        $keys = array_keys($vendors);

        $routine = $vendors[ $keys[0] ]->routine()->get()[0];

        $strategies = $routine->strategies()->get();

        return $strategies;
    }

    public function getDefaultRoutine()
    {
        return [

        ];

    }
}