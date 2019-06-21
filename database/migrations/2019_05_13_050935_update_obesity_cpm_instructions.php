<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class UpdateObesityCpmInstructions extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if ( ! isUnitTestingEnv()) {
            Artisan::call('db:seed', [
                '--class' => CpmProblemsTableSeeder::class,
            ]);

            Artisan::call('db:seed', [
                '--class' => CpmDefaultInstructionSeeder::class,
            ]);
        }
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
    }
}
