<?php

use App\Models\MedicalRecords\Ccda;
use App\User;
use Illuminate\Database\Seeder;

class CcdaImporterPredictionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Ccda::withTrashed()->get() as $ccda) {
            try {
                if ($ccda->patient_id) {
                    $patient = User::withTrashed()->find($ccda->patient_id);

                    if (!$patient) {
                        continue;
                    }

                    $ccda->location_id = $patient->preferred_contact_location ?? null;
                    $ccda->practice_id = $patient->primary_practice_id ?? null;
                    $ccda->billing_provider_id = $patient->billing_provider_id ?? null;
                    $ccda->save();

                    $docLog = $ccda->document;

                    if ($docLog) {
                        $docLog->location_id = $ccda->location_id ?? null;
                        $docLog->practice_id = $ccda->practice_id ?? null;
                        $docLog->billing_provider_id = $ccda->billing_provider_id ?? null;
                        $docLog->save();
                    }


                    $providersLog = $ccda->providers;

                    if ($providersLog) {
                        foreach ($providersLog as $providerLog) {
                            $providerLog->location_id = $ccda->location_id ?? null;
                            $providerLog->practice_id = $ccda->practice_id ?? null;
                            $providerLog->billing_provider_id = $ccda->billing_provider_id ?? null;
                            $providerLog->save();
                        }
                    }

                }
            } catch (Exception $e) {
                if ($e instanceof \Illuminate\Database\QueryException) {
                    $errorCode = $e->errorInfo[1];
                    if ($errorCode == 1452) {
                        //do nothing
                    }
                }
            }
        }
    }
}
