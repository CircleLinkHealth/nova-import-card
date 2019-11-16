<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\AppConfig;
use CircleLinkHealth\Customer\AppConfig\PatientSupportUser;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Migrations\Migration;

class AddPatientSupportNovaKey extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (isProductionEnv()) {
            $patientSupportUserId = 948;
        } else {
            $patientSupportUserId = User::create([
                'first_name'   => 'Patient',
                'last_name'    => 'Support',
                'display_name' => 'Patient Support',
                'email'        => 'patient.support@example.com',
                'username'     => 'patient.support',
            ])->id;
        }

        AppConfig::whereConfigKey(PatientSupportUser::PATIENT_SUPPORT_USER_ID_NOVA_KEY)->delete();

        AppConfig::updateOrCreate(
            [
                'config_key' => PatientSupportUser::PATIENT_SUPPORT_USER_ID_NOVA_KEY,
            ],
            [
                'config_value' => $patientSupportUserId,
            ]
        );
    }
}
