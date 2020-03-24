<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RenameSoftRejectedCallbackToRequestedCallback extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('enrollees', 'soft_rejected_callback')) {
            Schema::table('enrollees', function (Blueprint $table) {
                $table->renameColumn('soft_rejected_callback', 'requested_callback');
            });
        }
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
