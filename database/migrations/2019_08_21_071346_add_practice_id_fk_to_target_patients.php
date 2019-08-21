<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\TargetPatient;
use CircleLinkHealth\Customer\Entities\Practice;
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
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('target_patients', function (Blueprint $table) {
            $table->unsignedInteger('practice_id')->after('id');
        });

        App\TargetPatient::select('ehr_practice_id')->groupBy('ehr_practice_id')->get()->each(function ($tpPractice) {
            $practice = Practice::where('external_id', $tpPractice->ehr_practice_id)->firstOrFail();
            TargetPatient::where('ehr_practice_id', $tpPractice->ehr_practice_id)->update(['practice_id' => $practice->id]);
        });

        Schema::table('target_patients', function (Blueprint $table) {
            $table->foreign('practice_id')->references('id')->on('practices')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
