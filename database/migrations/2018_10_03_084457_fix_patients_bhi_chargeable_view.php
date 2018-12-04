<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class FixPatientsBhiChargeableView extends Migration
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
        \Artisan::call('view:CreateOrReplacePatientsBhiChargeableViewTable');
    }
}
