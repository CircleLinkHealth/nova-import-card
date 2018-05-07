<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->unsignedInteger('medication_group_id');
            $table->timestamps();

            $table->foreign('medication_group_id')
                ->references('id')
                ->on('cpm_medication_groups')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('medication_groups_maps');
    }
}
