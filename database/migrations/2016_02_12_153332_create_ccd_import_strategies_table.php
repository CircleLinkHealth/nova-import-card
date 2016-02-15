<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCcdImportStrategiesTable extends Migration {

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
			$table->integer('ccd_vendor_id')->unsigned();
			$table->integer('importer_section_id')->unsigned();
			$table->integer('validator_id')->unsigned();
			$table->integer('parser_id')->unsigned();
			$table->integer('storage_id')->unsigned();
			$table->timestamps();

			$table->foreign('ccd_vendor_id')->references('id')->on('ccd_vendors');
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
			$table->dropForeign('ccd_import_routines_strategies_ccd_vendor_id_foreign');
		});

		Schema::drop('ccd_import_routines_strategies');
	}

}
