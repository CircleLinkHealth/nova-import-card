<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimeZones extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tz_countries')) {
            Schema::create('tz_countries', function (Blueprint $table) {
                $table->string('country_code')->primary();
                $table->string('country_name');
            });   
        }
        
        if (!Schema::hasTable('tz_zones')) {
            Schema::create('tz_zones', function (Blueprint $table) {
                $table->increments('zone_id');
                $table->string('country_code');
                $table->string('zone_name');
                $table->index([ 'zone_name', 'country_code' ]);
                $table->foreign('country_code')->references('country_code')->on('tz_countries');
            });
        }

        if (!Schema::hasTable('tz_timezones')) {
            Schema::create('tz_timezones', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('zone_id')->unsigned();
                $table->string('abbreviation', 6);
                $table->decimal('time_start', 11, 0);
                $table->integer('gmt_offset');
                $table->char('dst', 1);
                $table->index([ 'zone_id', 'time_start' ]);
                $table->foreign('zone_id')->references('zone_id')->on('tz_zones');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        if (Schema::hasTable('tz_timezones')) Schema::drop('tz_timezones');
        if (Schema::hasTable('tz_zones')) Schema::drop('tz_zones');
        if (Schema::hasTable('tz_countries')) Schema::drop('tz_countries');
    }
}
