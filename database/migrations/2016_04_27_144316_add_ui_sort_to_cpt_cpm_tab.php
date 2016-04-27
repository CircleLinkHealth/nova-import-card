<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUiSortToCptCpmTab extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('care_plan_templates_cpm_lifestyles', function(Blueprint $table)
		{
			$table->unsignedInteger('ui_sort')
				->after('id')
				->nullable()
				->default(null);
		});

		Schema::table('care_plan_templates_cpm_symptoms', function(Blueprint $table)
		{
			$table->unsignedInteger('ui_sort')
				->after('id')
				->nullable()
				->default(null);
		});

		Schema::table('care_plan_templates_cpm_medication_groups', function(Blueprint $table)
		{
			$table->unsignedInteger('ui_sort')
				->after('id')
				->nullable()
				->default(null);
		});

		Schema::table('care_plan_templates_cpm_problems', function(Blueprint $table)
		{
			$table->unsignedInteger('ui_sort')
				->after('id')
				->nullable()
				->default(null);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('care_plan_templates_cpm_lifestyles', function(Blueprint $table)
		{
			$table->dropColumn('ui_sort');
		});

		Schema::table('care_plan_templates_cpm_symptoms', function(Blueprint $table)
		{
			$table->dropColumn('ui_sort');
		});

		Schema::table('care_plan_templates_cpm_medication_groups', function(Blueprint $table)
		{
			$table->dropColumn('ui_sort');
		});

		Schema::table('care_plan_templates_cpm_problems', function(Blueprint $table)
		{
			$table->dropColumn('ui_sort');
		});
	}

}
