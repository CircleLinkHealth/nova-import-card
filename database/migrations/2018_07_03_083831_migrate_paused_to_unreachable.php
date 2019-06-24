<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Database\Migrations\Migration;

class MigratePausedToUnreachable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Patient::query()
            ->orderBy('id')
            ->where('date_paused', '<', Carbon::createFromDate(2018, 5, 1))
            ->where('ccm_status', Patient::PAUSED)
            ->chunk(500, function ($patients) {
                foreach ($patients as $p) {
                    $p->ccm_status = Patient::UNREACHABLE;
                    $p->date_unreachable = $p->date_paused;
                    $p->save();
                }
            });
    }
}
