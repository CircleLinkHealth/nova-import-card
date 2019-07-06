<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValidationChecks extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('imported_medical_records', function (Blueprint $table) {
            $table->dropColumn('validation_checks');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('imported_medical_records', function (Blueprint $table) {
            $table->json('validation_checks')
                ->after('duplicate_id')
                ->nullable();
        });
    }
}
