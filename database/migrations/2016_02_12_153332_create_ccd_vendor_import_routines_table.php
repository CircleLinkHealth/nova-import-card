<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCcdVendorImportRoutinesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ccd_vendor_import_routines', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('ccd_vendor_id');
			$table->string('importer_section_id');
			$table->string('validator_id');
			$table->string('parser_id');
			$table->string('storage_id');
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
		Schema::drop('ccd_vendor_import_routines');
	}

}
