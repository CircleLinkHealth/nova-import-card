<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Location;


use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;
use App\CLH\Facades\StringManipulation;
use App\Location;

class ProviderLocationParser implements ParsingStrategy
{
    public function parse($providers, ValidationStrategy $validator = null)
    {
        $providerInfo = array_map( function ($provider) {
            if ( isset($provider->address->street[ 0 ]) && isset($provider->phones[ 0 ]->number) ) {
                $info[ 'address' ] = $provider->address->street[ 0 ];
                $info[ 'phone' ] = StringManipulation::formatPhoneNumber( $provider->phones[ 0 ]->number );
                return $info;
            }
        }, $providers );

        $providerInfo = array_values( array_filter( $providerInfo ) );

        $locations = Location::where( 'address_line_1', $providerInfo[ 0 ][ 'address' ] )
            ->where( 'phone', $providerInfo[ 0 ][ 'phone' ] )
            ->whereNotNull( 'parent_id' )
            ->get();

        if ( count( $locations ) > 0 ) return $locations->all();

        return false;
    }
}