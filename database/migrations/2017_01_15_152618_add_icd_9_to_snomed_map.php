<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIcd9ToSnomedMap extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('snomed_to_icd9_map', function (Blueprint $table) {
            $table->increments('id');
            $table->string('icd_9_code');
            $table->string('icd_9_name');
            $table->double('icd_9_avg_usage');
            $table->boolean('icd_9_is_nec');
            $table->integer('snomed_code');
            $table->string('snomed_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('snomed_to_icd9_map');
    }
}
