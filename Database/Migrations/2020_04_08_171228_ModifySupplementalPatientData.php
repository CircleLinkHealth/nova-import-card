<?php

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\SupplementalPatientData;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\CarePlanHelper;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifySupplementalPatientData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('patient_data', 'supplemental_patient_data');
        
        Schema::table('supplemental_patient_data', function (Blueprint $table) {
            $table->unsignedInteger('billing_provider_user_id')->nullable()->after('id');
            $table->unsignedInteger('location_id')->nullable()->after('id');
            $table->unsignedInteger('practice_id')->after('id');
            $table->string('location')->nullable()->after('provider');
        });
        
        if (config('database.connections.mysql.database') === 'cpm_production') {
            SupplementalPatientData::where('id', '>', 0)->update([
                'practice_id' => Practice::whereName(CarePlanHelper::NBI_PRACTICE_NAME)->value('id')
                                            ]);
        } else {
            SupplementalPatientData::where('id', '>', 0)->update([
                                                'practice_id' => Practice::where('is_demo', true)->firstOrFail()
                                            ]);
        }

        Schema::table('supplemental_patient_data', function (Blueprint $table) {
            $table->foreign('billing_provider_user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('location_id')->references('id')->on('locations')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('practice_id')->references('id')->on('practices')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    
    }
}
