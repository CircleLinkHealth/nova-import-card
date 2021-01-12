<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCareCoachIdToIMR extends Migration
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
        Schema::table('imported_medical_records', function (Blueprint $table) {
            $table->unsignedInteger('nurse_user_id')->nullable()->after('billing_provider_id');

            $table->foreign('nurse_user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }
}
