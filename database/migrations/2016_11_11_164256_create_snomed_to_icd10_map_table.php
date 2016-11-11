<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSnomedToIcd10MapTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('snomed_to_icd10_map', function (Blueprint $table) {
            $table->bigInteger('snomed_code');
            $table->string('snomed_name');
            $table->string('icd_10_code');
            $table->string('icd_10_name');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('snomed_to_icd10_map');
    }

}
