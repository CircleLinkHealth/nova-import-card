<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class GenerateCalvaryEnrollmentLetter extends Migration
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
        if (class_exists($class = 'CircleLinkHealth\Eligibility\Database\Seeders\GenerateCalvaryClinicLetter'))
            Artisan::call('db:seed', ['--class' => $class]);
    }
}
