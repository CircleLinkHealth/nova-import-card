<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\CareTeam;


use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;
use App\WpUser as User;

class PrimaryProviderParser implements ParsingStrategy
{

    /**
     * @param $documentationOf
     * @param ValidationStrategy|null $validator
     * @return array|bool
     */
    public function parse($documentationOf, ValidationStrategy $validator = null)
    {
        if ( empty($documentationOf) ) return false;

        foreach ( $documentationOf as $doc )
        {
            if ( isset($doc->name->given[ 0 ]) && isset($doc->name->family) )
            {
                $doctorNames[] = $doc->name->given[ 0 ] . ' ' . $doc->name->family;
            }
        }

        if ( !isset($doctorNames) ) return false;

        $providers = User::whereHas( 'roles', function ($q) {
            $q->where( 'name', '=', 'provider' );
        } )->get();

        foreach ( $providers as $provider ) {
            if ( in_array( $provider->display_name, $doctorNames ) ) $careTeam[] = $provider;
        }

        return isset($careTeam) ? $careTeam : false;
    }
}