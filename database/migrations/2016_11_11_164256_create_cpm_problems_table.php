<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCpmProblemsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cpm_problems', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('icd10from');
            $table->string('icd10to');
            $table->float('icd9from');
            $table->float('icd9to');
            $table->text('contains', 65535);
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
        Schema::drop('cpm_problems');
    }

}
