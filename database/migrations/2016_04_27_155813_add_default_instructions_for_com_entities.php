<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultInstructionsForComEntities extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('cpm_problems', function (Blueprint $table) {
			$table->text('default_instructions')
				->nullable()
				->default(null)
				->before('created_at');
		});

		Schema::table('cpm_lifestyles', function (Blueprint $table) {
			$table->text('default_instructions')
				->nullable()
				->default(null)
				->before('created_at');
		});

		Schema::table('cpm_medication_groups', function (Blueprint $table) {
			$table->text('default_instructions')
				->nullable()
				->default(null)
				->before('created_at');
		});

		Schema::table('cpm_symptoms', function (Blueprint $table) {
			$table->text('default_instructions')
				->nullable()
				->default(null)
				->before('created_at');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('cpm_problems', function(Blueprint $table)
		{
			$table->dropColumn('default_instructions');
		});

		Schema::table('cpm_lifestyles', function(Blueprint $table)
		{
			$table->dropColumn('default_instructions');
		});

		Schema::table('cpm_medication_groups', function(Blueprint $table)
		{
			$table->dropColumn('default_instructions');
		});

		Schema::table('cpm_symptoms', function(Blueprint $table)
		{
			$table->dropColumn('default_instructions');
		});
	}

}
