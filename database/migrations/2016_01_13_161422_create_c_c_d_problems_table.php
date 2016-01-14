<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCCDProblemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ccd_problems', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('icd10from');
			$table->string('icd10to');
			$table->float('icd9from');
			$table->float('icd9to');
			$table->text('contains');
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
		Schema::drop('ccd_problems');
	}

}
