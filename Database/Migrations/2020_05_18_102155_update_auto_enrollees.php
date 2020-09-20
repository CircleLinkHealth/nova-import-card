<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Eligibility\SelfEnrollment\Jobs\CreateSurveyOnlyUserFromEnrollee;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Database\Migrations\Migration;

class UpdateAutoEnrollees extends Migration
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
        Enrollee::whereNull('user_id')
            ->where('status', Enrollee::QUEUE_AUTO_ENROLLMENT)
            ->select('id')
            ->chunk(100, function ($enrollees) {
                $enrollees->each(function ($enrollee) {
                    CreateSurveyOnlyUserFromEnrollee::dispatch($enrollee);
                });
            });
    }
}
