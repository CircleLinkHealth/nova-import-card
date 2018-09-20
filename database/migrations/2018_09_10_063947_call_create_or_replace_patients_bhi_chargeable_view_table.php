<?php

use Illuminate\Database\Migrations\Migration;

class CallCreateOrReplacePatientsBhiChargeableViewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Artisan::call('view:CreateOrReplacePatientsBhiChargeableViewTable');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
