<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

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
			$table->integer('program_id')->unsigned()->nullable()->index('ccd_vendors_program_id_foreign');
			$table->integer('ccd_import_routine_id')->unsigned();
			$table->string('vendor_name');
			$table->string('ehr_name')->nullable();
			$table->string('practice_id')->nullable();
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
