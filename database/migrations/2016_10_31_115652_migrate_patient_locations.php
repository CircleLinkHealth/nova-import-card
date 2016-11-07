<?php

use App\PatientInfo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigratePatientLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (PatientInfo::all() as $patient) {
            try {
                $patient->user->locations()->attach($patient->preferred_contact_location);
            } catch (\Exception $e) {
                Log::alert("Location {$patient->preferred_contact_location} for patient id {$patient->user_id} does not exist.");
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
        Schema::table('patient_info', function (Blueprint $table) {
            //
        });
    }
}
