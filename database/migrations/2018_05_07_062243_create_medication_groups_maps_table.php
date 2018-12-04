<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMedicationGroupsMapsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medication_groups_maps', function (Blueprint $table) {
            $table->increments('id');
            $table->string('keyword');
            $table->integer('medication_group_id')->unsigned()->index('medication_groups_maps_medication_group_id_foreign');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('medication_groups_maps');
    }
}
