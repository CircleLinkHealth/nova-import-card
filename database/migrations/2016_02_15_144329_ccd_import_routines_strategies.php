<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CcdImportRoutinesStrategies extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ccd_import_routines_strategies', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('ccd_import_routine_id')->unsigned();
			$table->integer('importer_section_id')->unsigned();
			$table->integer('validator_id')->unsigned();
			$table->integer('parser_id')->unsigned();
			$table->integer('storage_id')->unsigned();
			$table->timestamps();

			$table->foreign('ccd_import_routine_id')->references('id')->on('ccd_import_routines');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ccd_import_routines_strategies', function(Blueprint $table) {
			$table->dropForeign('ccd_import_routines_strategies_ccd_import_routine_id_foreign');
		});

		Schema::drop('ccd_import_routines_strategies');
	}

}
