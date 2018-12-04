<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePhoenixHeartMedicationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phoenix_heart_medications', function (Blueprint $table) {
            $table->integer('patient_id')->nullable();
            $table->string('description')->nullable();
            $table->string('instructions')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->string('stop_reason')->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('phoenix_heart_medications');
    }
}
