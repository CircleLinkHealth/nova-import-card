<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSnomedToIcdMapTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('snomed_to_icd10_map', function(Blueprint $table) {
			$table->bigInteger('snomedCode');
			$table->string('snomedName');
			$table->string('icd10Code');
			$table->string('icd10Name');
		});


	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('snomed_to_icd10_map');
	}

}
