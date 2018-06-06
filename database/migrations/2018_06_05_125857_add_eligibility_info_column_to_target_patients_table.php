<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEligibilityInfoColumnToTargetPatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('target_patients', function (Blueprint $table) {
            $table->text('eligibility_job_id')->nullable()->after('batch_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('target_patients', function (Blueprint $table) {
            $table->dropColumn('eligibility_job_id');
        });
    }
}
