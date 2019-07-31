<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifySpecificPatientRequestsToJsonInProviderReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provider_reports', function (Blueprint $table) {
            $table->json('specific_patient_requests')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provider_reports', function (Blueprint $table) {
            $table->string('specific_patient_requests')->change();
        });
    }
}
