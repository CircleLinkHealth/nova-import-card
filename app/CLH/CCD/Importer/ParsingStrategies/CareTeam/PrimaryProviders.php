<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\CareTeam;


use App\CLH\CCD\Ccda;
use App\CLH\CCD\ItemLogger\CcdProviderLog;
use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;
use App\CLH\Contracts\Repositories\UserRepository;
use App\ForeignId;
use App\User;

class PrimaryProviders implements ParsingStrategy
{
    private $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

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
                $doctorNames[$doc->id] = $doc->first_name . ' ' . $doc->last_name;
            }
        }

        if ( !isset($doctorNames) ) return false;

        $providers = $this->users->findByRole('provider');

        foreach ( $doctorNames as $docId => $docName ) {
            $matchedProviders = $providers->where('display_name', $docName)->all();

            foreach ($matchedProviders as $provider){
                $providerLog = CcdProviderLog::find($docId);
                $providerLog->import = true;
                $providerLog->save();

                /**
                 * BAD!
                 * @todo: make EHR models after done with Aprima API
                 */
                if (isset($providerLog->provider_id)) {
                    $attributes = [
                        'user_id' => $provider->ID,
                        'foreign_id' => $providerLog->provider_id,
                        'system' => ForeignId::APRIMA
                    ];
                    
                    $foreignId = ForeignId::updateOrCreate($attributes, $attributes);
                }

                $careTeam[] = $provider;
            }
        }

        return isset($careTeam) ? $careTeam : false;
    }
}