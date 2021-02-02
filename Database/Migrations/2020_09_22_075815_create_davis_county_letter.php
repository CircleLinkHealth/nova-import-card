<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

class CreateDavisCountyLetter extends Migration
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
        Artisan::call('db:seed', ['--class' => 'CircleLinkHealth\SelfEnrollment\Database\Seeders\GenerateDavisCountyLetter']);
    }
}
