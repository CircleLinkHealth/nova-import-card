<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\CareTeam;


use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;
use App\CLH\Contracts\Repositories\UserRepository;
use App\ForeignId;
use App\Importer\Models\ItemLogs\ProviderLog;
use App\Models\MedicalRecords\Ccda;

class PrimaryProviders implements ParsingStrategy
{
    private $users;
    private $locationId;

    public function __construct(UserRepository $users, $locationId)
    {
        $this->users = $users;
        $this->locationId;
    }

    /**
     * @param $documentationOf
     * @param ValidationStrategy|null $validator
     * @return array|bool
     */
    public function parse(Ccda $ccd, ValidationStrategy $validator = null)
    {
        $documentationOf = ProviderLog::whereCcdaId($ccd->id)->get();

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
                $providerLog = ProviderLog::find($docId);
                $providerLog->import = true;
                $providerLog->save();

                /**
                 * BAD!
                 * @todo: make EHR models after done with Aprima API
                 */
                if (isset($providerLog->provider_id)) {
                    $attributes = [
                        'user_id'     => $provider->id,
                        'foreign_id'  => $providerLog->provider_id,
                        'system'      => ForeignId::APRIMA,
                        'location_id' => empty($this->locationId) ? null : $this->locationId,
                    ];

                    try {
                        $foreignId = ForeignId::updateOrCreate($attributes);
                    } catch (\Exception $e) {
                        //check if this is a mysql exception for unique key constraint
                        if ($e instanceof \Illuminate\Database\QueryException) {
                            $errorCode = $e->errorInfo[1];
                            if ($errorCode == 1062) {
                                //do nothing
                                //we don't actually want to terminate the program if we detect duplicates
                                //we just don't wanna add the row again
                                \Log::alert($e);
                            }
                        }
                    }

                }

                $careTeam[] = $provider;
            }
        }

        return isset($careTeam) ? $careTeam : false;
    }
}