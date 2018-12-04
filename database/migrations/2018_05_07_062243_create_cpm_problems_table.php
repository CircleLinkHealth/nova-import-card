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
            $table->string('default_icd_10_code', 20)->nullable();
            $table->string('name');
            $table->string('icd10from');
            $table->string('icd10to');
            $table->float('icd9from');
            $table->float('icd9to');
            $table->text('contains', 65535);
            $table->boolean('is_behavioral')->default(0);
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
