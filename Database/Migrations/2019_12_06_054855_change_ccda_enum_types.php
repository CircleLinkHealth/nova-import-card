<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Database\Migrations\Migration;

class ChangeCcdaEnumTypes extends Migration
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
        $statuses = "'".implode("', '", [
            Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY,
            Ccda::ELIGIBLE,
            Ccda::ERROR,
            Ccda::IMPORT,
            Ccda::INELIGIBLE,
            Ccda::INVALID,
            Ccda::PATIENT_CONSENTED,
            Ccda::PATIENT_DECLINED,
            Ccda::QA,
        ])."'";

        DB::statement("ALTER TABLE ccdas CHANGE COLUMN status status ENUM($statuses) NULL");
    }
}
