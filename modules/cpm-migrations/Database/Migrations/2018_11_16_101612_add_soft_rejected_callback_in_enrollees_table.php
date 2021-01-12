<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftRejectedCallbackInEnrolleesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasColumn('enrollees', 'soft_rejected_callback')) {
            Schema::table('enrollees', function (Blueprint $table) {
                $table->dropColumn('soft_rejected_callback');
            });
        }
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            $table->date('soft_rejected_callback')->nullable();
        });
    }
}
