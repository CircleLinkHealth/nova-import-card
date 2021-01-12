<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPracticeIdFkToTargetPatients extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('target_patients', function (Blueprint $table) {
            $table->dropForeign(['practice_id']);
            $table->dropColumn('practice_id');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        if (Schema::hasColumn('target_patients', 'practice_id')) {
            return;
        }

        Schema::table('target_patients', function (Blueprint $table) {
            $table->unsignedInteger('practice_id')->after('id');
        });

        \CircleLinkHealth\Eligibility\Entities\TargetPatient::select('ehr_practice_id')->groupBy('ehr_practice_id')->get()->each(function ($tpPractice) {
            $practice = Practice::where('external_id', $tpPractice->ehr_practice_id)->firstOrFail();
            TargetPatient::where('ehr_practice_id', $tpPractice->ehr_practice_id)->update(['practice_id' => $practice->id]);
        });

        Schema::table('target_patients', function (Blueprint $table) {
            $table->foreign('practice_id')->references('id')->on('practices')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
