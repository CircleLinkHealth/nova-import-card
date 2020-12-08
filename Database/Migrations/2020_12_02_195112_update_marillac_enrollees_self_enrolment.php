<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Console\Commands\UpdateEnrolleeProvidersThatCreatedWrong;
use Illuminate\Database\Migrations\Migration;

class UpdateMarillacEnrolleesSelfEnrolment extends Migration
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
        if (App::environment('production')) {
            Artisan::call(UpdateEnrolleeProvidersThatCreatedWrong::class);
        }
    }
}
