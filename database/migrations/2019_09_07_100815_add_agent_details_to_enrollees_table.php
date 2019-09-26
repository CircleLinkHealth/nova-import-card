<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAgentDetailsToEnrolleesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            $table->dropColumn('agent_details');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            $table->json('agent_details')->nullable();
        });
    }
}
