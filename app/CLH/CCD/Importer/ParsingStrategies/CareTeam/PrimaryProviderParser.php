<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\CareTeam;


use App\CLH\CCD\Ccda;
use App\CLH\CCD\ItemLogger\CcdProviderLog;
use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;
use App\User;

class PrimaryProviderParser implements ParsingStrategy
{

    /**
     * @param $documentationOf
     * @param ValidationStrategy|null $validator
     * @return array|bool
     */
    public function parse(Ccda $ccd, ValidationStrategy $validator = null)
    {
        $documentationOf = CcdProviderLog::whereCcdaId($ccd->id)->get();

        if ( empty($documentationOf) ) return false;

        foreach ( $documentationOf as $doc )
        {
            if ( isset($doc->first_name) && isset($doc->last_name) )
            {
                $doctorNames[] = $doc->first_name . ' ' . $doc->last_name;
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