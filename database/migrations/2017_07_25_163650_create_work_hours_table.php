<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkHoursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_hours', function (Blueprint $table) {
            $table->increments('id');
            $table->string('workhourable_type');
            $table->unsignedInteger('workhourable_id');
            $table->unsignedInteger('monday')->nullable();
            $table->unsignedInteger('tuesday')->nullable();
            $table->unsignedInteger('wednesday')->nullable();
            $table->unsignedInteger('thursday')->nullable();
            $table->unsignedInteger('friday')->nullable();
            $table->unsignedInteger('saturday')->nullable();
            $table->unsignedInteger('sunday')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_hours');
    }
}
