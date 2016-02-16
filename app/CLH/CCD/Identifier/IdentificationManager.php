<?php

namespace App\CLH\CCD\Identifier;


use App\CLH\CCD\Identifier\IdentificationStrategies\BaseIdentificationStrategy;

class IdentificationManager extends BaseIdentificationStrategy
{
    protected $identifiers = [
        'custodian_name' => null,
        'doctor_name' => null,
        'doctor_oid' => null,
        'ehr_oid' => null,
    ];

    public function identify()
    {
        $identifierMap = \Config::get( 'ccdimportervendoridentifiermap' );

        foreach ( $identifierMap as $field => $identifiers ) {
            foreach ( $identifiers as $identifier ) {
                if ( !empty($this->identifiers[ $field ]) ) continue 2;
                $this->identifiers[ $field ] = ( new $identifier[ 'class' ]( $this->ccd ) )->identify();
            }
        }

        //this will get rid of empty entries
        $filteredIdentifiers = array_filter( $this->identifiers );

        if ( !$filteredIdentifiers ) {
            // all identifier values are false
            return false;
        }

        return $filteredIdentifiers;
    }
}