<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdvancedCarePlanningColumnToProviderReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provider_reports', function (Blueprint $table) {
            $table->json('advanced_care_planning')->nullable()->default('null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provider_reports', function (Blueprint $table) {
            $table->dropColumn('advanced_care_planning');
        });
    }
}
