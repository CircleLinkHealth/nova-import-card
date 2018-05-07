<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSnomedToCpmIcdMapsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('snomed_to_cpm_icd_maps', function(Blueprint $table)
		{
			$table->increments('id');
			$table->bigInteger('snomed_code');
			$table->string('snomed_name');
			$table->string('icd_10_code');
			$table->string('icd_10_name');
			$table->timestamps();
			$table->string('icd_9_code');
			$table->string('icd_9_name');
			$table->float('icd_9_avg_usage', 10, 0);
			$table->boolean('icd_9_is_nec');
			$table->integer('cpm_problem_id')->unsigned()->nullable()->index('snomed_to_cpm_icd_maps_cpm_problem_id_foreign');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('snomed_to_cpm_icd_maps');
	}

}
