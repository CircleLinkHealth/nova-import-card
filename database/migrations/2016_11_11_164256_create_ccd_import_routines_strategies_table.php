<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCcdImportRoutinesStrategiesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ccd_import_routines_strategies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ccd_import_routine_id')->unsigned()->index('ccd_import_routines_strategies_ccd_import_routine_id_foreign');
            $table->integer('importer_section_id')->unsigned();
            $table->integer('validator_id')->unsigned();
            $table->integer('parser_id')->unsigned();
            $table->integer('storage_id')->unsigned();
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
        Schema::drop('ccd_import_routines_strategies');
    }

}
