<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DayOfWeek extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('days_of_week')) {
            Schema::create('days_of_week', function (Blueprint $table) {
                $table->integer('id')->unique();
                $table->string('name');
                $table->string('abbreviation', 2);
                $table->index([ 'id' ]);
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
        if (Schema::hasTable('days_of_week')) Schema::drop('days_of_week');
    }
}
