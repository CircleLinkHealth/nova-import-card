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
            $table->unsignedInteger('monday')->default(0);
            $table->unsignedInteger('tuesday')->default(0);
            $table->unsignedInteger('wednesday')->default(0);
            $table->unsignedInteger('thursday')->default(0);
            $table->unsignedInteger('friday')->default(0);
            $table->unsignedInteger('saturday')->default(0);
            $table->unsignedInteger('sunday')->default(0);
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
