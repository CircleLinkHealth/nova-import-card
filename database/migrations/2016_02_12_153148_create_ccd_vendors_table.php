<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCcdVendorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ccd_vendors', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('ccd_import_routine_id');
			$table->string('ehr_name');

			$table->integer('ehr_oid')->nullable();
			$table->string('doctor_name')->nullable();
			$table->integer('doctor_oid')->nullable();
			$table->string('custodian_name')->nullable();

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
		Schema::drop('ccd_vendors');
	}

}
