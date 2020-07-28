<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class AddCaLoadingTimeToEnrollees extends Migration
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
        \CircleLinkHealth\Customer\Entities\User::ofType('care-ambassador')
            ->with(['assignedEnrollees' => function ($e) {
                $e->whereNotNull('last_attempt_at');
            },
                'pageTimersAsProvider' => function ($pt) {
                    $pt->whereNull('enrollee_id');
                }, ])
            ->chunk(2, function ($caUsers) {
                foreach ($caUsers as $ca) {
                    $enrollees = $ca->assignedEnrollees->all();

                    if (empty($enrollees)) {
                        continue;
                    }

                    $enrolleesCount = count($enrollees);

                    $loadingTimers = $ca->pageTimersAsProvider->all();

                    if (empty($loadingTimers)) {
                        continue;
                    }

                    $loadingTimersCount = count($loadingTimers);

                    $i = 0;
                    $enrolleeIndex = 0;
                    while ($i < $loadingTimersCount) {
                        $timer = $loadingTimers[$i];

                        $enrolleeIndex = $enrolleeIndex < $enrolleesCount ? $enrolleeIndex : $enrolleeIndex - $enrolleesCount;

                        $enrollee = $enrollees[$enrolleeIndex];

                        $timer->enrollee_id = $enrollee->id;
                        $timer->save();

                        ++$i;
                        ++$enrolleeIndex;
                    }
                }
            });
    }
}
