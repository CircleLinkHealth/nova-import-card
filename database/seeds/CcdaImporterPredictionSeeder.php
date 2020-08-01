<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Database\Seeder;

class CcdaImporterPredictionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach (Ccda::withTrashed()->get() as $ccda) {
            try {
                if ($ccda->patient_id) {
                    $patient = User::withTrashed()->find($ccda->patient_id);

                    if ( ! $patient) {
                        continue;
                    }

                    $ccda->location_id         = $patient->getPreferredContactLocation() ?? null;
                    $ccda->practice_id         = $patient->getPrimaryPracticeId() ?? null;
                    $ccda->billing_provider_id = $patient->getBillingProviderId() ?? null;
                    $ccda->save();

                    $docLog = $ccda->document;

                    if ($docLog) {
                        $docLog->location_id         = $ccda->location_id ?? null;
                        $docLog->practice_id         = $ccda->practice_id ?? null;
                        $docLog->billing_provider_id = $ccda->billing_provider_id ?? null;
                        $docLog->save();
                    }

                    $providersLog = $ccda->providers;

                    if ($providersLog) {
                        foreach ($providersLog as $providerLog) {
                            $providerLog->location_id         = $ccda->location_id ?? null;
                            $providerLog->practice_id         = $ccda->practice_id ?? null;
                            $providerLog->billing_provider_id = $ccda->billing_provider_id ?? null;
                            $providerLog->save();
                        }
                    }
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
