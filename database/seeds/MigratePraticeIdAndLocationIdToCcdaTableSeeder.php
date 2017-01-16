<?php

use App\Models\MedicalRecords\Ccda;
use App\User;
use Illuminate\Database\Seeder;

class MigratePraticeIdAndLocationIdToCcdaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Ccda::withTrashed()->get() as $ccda) {
            if ($ccda->patient_id) {
                $patient = User::find($ccda->patient_id);

                if (!$patient) {
                    continue;
                }

                $ccda->location_id = $patient->preferred_contact_location;
                $ccda->practice_id = $patient->primary_practice_id;
                $ccda->save();

                $docLog = $ccda->document;

                if ($docLog) {
                    $docLog->location_id = $ccda->location_id;
                    $docLog->practice_id = $ccda->practice_id;
                    $docLog->save();
                }


                $providersLog = $ccda->providers;

                if ($providersLog) {
                    foreach ($providersLog as $providerLog) {
                        $providerLog->location_id = $ccda->location_id;
                        $providerLog->practice_id = $ccda->practice_id;
                        $providerLog->save();
                    }
                }

            }
        }
    }
}
