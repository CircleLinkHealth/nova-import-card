<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RenameSoftRejectedCallbackToRequestedCallback extends Migration
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
        if (Schema::hasColumn('enrollees', 'soft_rejected_callback')) {
            Schema::table('enrollees', function (Illuminate\Database\Schema\Blueprint $table) {
                $table->renameColumn('soft_rejected_callback', 'requested_callback');
            });
        }
    }
}
