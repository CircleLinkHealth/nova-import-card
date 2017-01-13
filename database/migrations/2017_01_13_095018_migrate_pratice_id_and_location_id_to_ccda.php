<?php

use App\Models\MedicalRecords\Ccda;
use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigratePraticeIdAndLocationIdToCcda extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccdas', function (Blueprint $table) {
            //
        });
    }
}
