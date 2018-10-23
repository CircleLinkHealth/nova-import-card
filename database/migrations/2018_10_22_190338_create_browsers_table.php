<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBrowsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('browsers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('minimum_version');
            $table->date('release_date');
        });

        DB::table('browsers')->insert([
            ['name' => 'Chrome', 'minimum_version' => '54.0.2840', 'release_date' => Carbon::parse('2016-10-12')],
            ['name' => 'Safari', 'minimum_version' => '10.0.1', 'release_date' => Carbon::parse('2016-10-24')],
            ['name' => 'Opera', 'minimum_version' => '41', 'release_date' => Carbon::parse('2016-10-25') ],
            ['name' => 'Firefox', 'minimum_version' => '50', 'release_date' => Carbon::parse('2016-11-14')],
            ['name' => 'Edge', 'minimum_version' => '39.14951', 'release_date' => Carbon::parse('2016-10-19')],
            ['name' => 'UCBrowser', 'minimum_version' => '11.0.5.850', 'release_date' => Carbon::parse('2016-9-18') ],
            ['name' => 'Vivaldi', 'minimum_version' => '1.4', 'release_date' => Carbon::parse('2016-9-8')],
            ['name' => 'Mozilla', 'minimum_version' => '1.7.13', 'release_date' => Carbon::parse('2016-4-21')],

//            ['name' => 'IE', 'minimum_version' => '11.0', 'release_date' => '2013-10-17'],
//            ['name' => 'Netscape', 'minimum_version' => '9.0.0.6', 'release_date' => '2008-2-20' ],

        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('browsers');
    }
}
