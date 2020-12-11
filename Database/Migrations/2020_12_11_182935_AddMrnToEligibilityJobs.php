<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMrnToEligibilityJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('eligibility_jobs', function (Blueprint $table) {
            $table->dropColumn('patient_mrn');
        });
    
        Schema::table('eligibility_jobs', function (Blueprint $table) {
            $table->string('patient_mrn')->virtualAs('COALESCE(JSON_UNQUOTE(data->"$.mrn_number"),JSON_UNQUOTE(data->"$.mrn"))')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
