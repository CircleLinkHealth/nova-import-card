<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToEnrolleesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            $table->dropColumn('provider_pronunciation');
            $table->dropColumn('provider_sex');
            $table->dropColumn('last_office_visit_at');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            $table->string('provider_pronunciation')->nullable();
            $table->string('provider_sex')->nullable();
            $table->date('last_office_visit_at')->nullable();
        });
    }
}
