<?php

use Illuminate\Database\Migrations\Migration;

class CallCreateOrReplacePatientsCcmViewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Artisan::call('view:CreateOrReplacePatientsCcmViewTable');
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
