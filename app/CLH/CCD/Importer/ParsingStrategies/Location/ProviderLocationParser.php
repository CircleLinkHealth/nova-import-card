<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Location;


use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;
use App\Location;

class ProviderLocationParser implements ParsingStrategy
{
    public function parse($provider, ValidationStrategy $validator = null)
    {
        if ( !isset($provider->address->street[ 0 ]) ) return false;

        if ( empty($address = $provider->address->street[ 0 ]) ) return false;

        $locations = Location::where( 'address_line_1', $address )
            ->whereNotNull('parent_id')
            ->get();

        if ( count( $locations ) > 0 ) return $locations->all();

        return false;
    }
}