<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCompanyHolidaysTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('company_holidays');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('company_holidays', function (Blueprint $table) {
            $table->increments('id');
            $table->string('holiday_name')->nullable();
            $table->date('holiday_date');
            $table->timestamps();
        });

        DB::table('company_holidays')->insert([
            ['holiday_name' => 'Thanksgiving', 'holiday_date' => '2018-11-22'],
            ['holiday_name' => 'Christmas', 'holiday_date' => '2018-12-25'],
            ['holiday_name' => "New Year's day", 'holiday_date' => '2019-01-01'],
            ['holiday_name' => 'Memorial Day', 'holiday_date' => '2019-05-27'],
            ['holiday_name' => 'July 4th', 'holiday_date' => '2019-07-04'],
            ['holiday_name' => 'Labor Day', 'holiday_date' => '2019-09-02'],
            ['holiday_name' => 'Thanksgiving', 'holiday_date' => '2019-11-28'],
            ['holiday_name' => 'Christmas', 'holiday_date' => '2019-12-25'],
            ['holiday_name' => "New Year's day", 'holiday_date' => '2020-01-01'],
            ['holiday_name' => 'Memorial Day', 'holiday_date' => '2020-05-25'],
            ['holiday_name' => 'July 4th', 'holiday_date' => '2020-07-04'],
            ['holiday_name' => 'Labor Day', 'holiday_date' => '2020-09-07'],
            ['holiday_name' => 'Thanksgiving', 'holiday_date' => '2020-11-26'],
            ['holiday_name' => 'Christmas', 'holiday_date' => '2020-12-25'],
        ]);
    }
}
