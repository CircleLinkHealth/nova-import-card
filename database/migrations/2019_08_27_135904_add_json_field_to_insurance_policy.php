<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJsonFieldToInsurancePolicy extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('insurance_logs', function (Blueprint $table) {
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('insurance_logs', function (Blueprint $table) {
            $table->json('raw')->after('import')->nullable();
        });
    }
}
