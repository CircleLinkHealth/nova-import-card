<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\CCD\Identifier;

use App\CLH\CCD\Identifier\IdentificationStrategies\BaseIdentificationStrategy;

class IdentificationManager extends BaseIdentificationStrategy
{
    protected $matchedIdentifiers = [
        'custodian_name' => null,
        'doctor_name'    => null,
        'doctor_oid'     => null,
        'ehr_oid'        => null,
    ];

    public function identify()
    {
        $identifierMap
            
        
        
            = \config('ccdimportervendoridentifiermap');

        /*
         * Extracts Identifier Values from the CCD.
         * This function calls all the Identifiers from config/ccdimportervendoridentifiermap
         */
        foreach ($identifierMap as $field => $identifiers) {
            foreach ($identifiers as $identifier) {
                if ( ! empty($this->matchedIdentifiers[$field])) {
                    continue 2;
                }
                $this->matchedIdentifiers[$field] = (new $identifier['class']($this->ccd))->identify();
            }
        }

        //this will get rid of empty entries
        $filteredIdentifiers = array_filter($this->matchedIdentifiers);

        if ( ! $filteredIdentifiers) {
            // all identifier values are false
            return false;
        }

        return $filteredIdentifiers;
    }
}
