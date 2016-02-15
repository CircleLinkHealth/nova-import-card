<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCcdVendorIdentifiersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ccd_vendor_identifiers', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('ccd_vendor_id')->unsigned()->default(null);
			$table->integer('identifier_id')->unsigned();
			$table->integer('value')->unsigned();
			$table->boolean('exactMatch')->default(false);
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
		Schema::drop('ccd_vendor_identifiers');
	}

}
