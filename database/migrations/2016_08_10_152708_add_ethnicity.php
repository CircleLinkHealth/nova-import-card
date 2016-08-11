<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEthnicity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccd_demographics_logs', function (Blueprint $table) {
            $table->string('ethnicity')
                ->nullable()
                ->default(null)
                ->after('language');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccd_demographics_logs', function (Blueprint $table) {
            $table->dropColumn('ethnicity');
        });
    }
}
