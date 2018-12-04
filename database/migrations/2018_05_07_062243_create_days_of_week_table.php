<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDaysOfWeekTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('days_of_week', function (Blueprint $table) {
            $table->integer('id')->index();
            $table->string('name');
            $table->string('abbreviation', 2);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('days_of_week');
    }
}
