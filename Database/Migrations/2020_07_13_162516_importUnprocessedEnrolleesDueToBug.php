<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Eligibility\Jobs\ImportConsentedEnrollees;
use Illuminate\Database\Migrations\Migration;

class ImportUnprocessedEnrolleesDueToBug extends Migration
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
        $users = \CircleLinkHealth\Customer\Entities\User::ofType('survey-only')
            ->with(['enrollee.batch'])
            ->whereHas('enrollee', function ($e) {
                $e->where('status', \CircleLinkHealth\Eligibility\Entities\Enrollee::CONSENTED)
                    ->whereNotNull('care_ambassador_user_id')
                    ->whereHas('ccda', function ($ccda) {
                        $ccda->where('imported', false);
                    });
            })
            ->get();

        foreach ($users as $user) {
            $enrollee = $user->enrollee;

            ImportConsentedEnrollees::dispatch([$enrollee->id], $enrollee->batch);
        }
    }
}
