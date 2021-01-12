<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUiRequestsToEnrollmentLetter extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enrollment_invitation_letters', function (Blueprint $table) {
            $table->dropColumn('ui_requests');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollment_invitation_letters', function (Blueprint $table) {
            $table->json('ui_requests')->nullable()->default(null);
        });
    }
}
