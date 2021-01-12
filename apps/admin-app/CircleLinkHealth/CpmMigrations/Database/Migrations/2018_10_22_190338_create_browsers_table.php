<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrowsersTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('browsers');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('browsers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('warning_version');
            $table->string('required_version')->nullable();
            $table->date('release_date');
        });

        DB::table('browsers')->insert([
            ['name' => 'Chrome', 'warning_version' => '54.0.2840', 'required_version' => '66.0.3359', 'release_date' => Carbon::parse('2016-10-12')],
            ['name' => 'Safari', 'warning_version' => '10.0.1', 'required_version' => null, 'release_date' => Carbon::parse('2016-10-24')],
            ['name' => 'Opera', 'warning_version' => '41', 'required_version' => null, 'release_date' => Carbon::parse('2016-10-25')],
            ['name' => 'Firefox', 'warning_version' => '50', 'required_version' => null, 'release_date' => Carbon::parse('2016-11-14')],
            ['name' => 'Edge', 'warning_version' => '39.14951', 'required_version' => null, 'release_date' => Carbon::parse('2016-10-19')],
            ['name' => 'UCBrowser', 'warning_version' => '11.0.5.850', 'required_version' => null, 'release_date' => Carbon::parse('2016-9-18')],
            ['name' => 'Vivaldi', 'warning_version' => '1.4', 'required_version' => null, 'release_date' => Carbon::parse('2016-9-8')],
            ['name' => 'Mozilla', 'warning_version' => '1.7.13', 'required_version' => null, 'release_date' => Carbon::parse('2016-4-21')],
            ['name' => 'IE', 'warning_version' => '11.0', 'required_version' => null, 'release_date' => Carbon::parse('2013-10-17')],
            ['name' => 'Netscape', 'warning_version' => '9.0.0.6', 'required_version' => null, 'release_date' => Carbon::parse('2008-2-20')],
        ]);
    }
}
