<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class AddEpicIntoEhrsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \CircleLinkHealth\Customer\Entities\Ehr::where('name', 'Epic')->delete();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \CircleLinkHealth\Customer\Entities\Ehr::updateOrCreate([
            'name' => 'Epic',
        ], ['pdf_report_handler' => '']);
    }
}
