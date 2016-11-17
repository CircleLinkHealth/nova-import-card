<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInstructablesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instructables', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cpm_instruction_id')->unsigned()->index('instructables_cpm_instruction_id_foreign');
            $table->integer('instructable_id')->unsigned();
            $table->string('instructable_type');
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
        Schema::drop('instructables');
    }

}
