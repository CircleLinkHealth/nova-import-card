<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\SelfEnrollment\Jobs\CreateUsersFromEnrollees;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
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
            ->get()
            ->chunk(100)->each(function ($col) {
                $arr = $col
                    ->map(function ($item) {
                        return $item->id;
                    })
                    ->toArray();
                CreateUsersFromEnrollees::dispatch($arr);
            });
    }
}
