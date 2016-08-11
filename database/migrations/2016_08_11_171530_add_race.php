<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRace extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccd_demographics_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('ccd_demographics_logs', 'race')) {
                Schema::table('ccd_demographics_logs', function (Blueprint $table) {
                    $table->string('race')
                        ->nullable()
                        ->default(null)
                        ->after('language');
                });
            }
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
            $table->dropColumn('race');
        });
    }
}
