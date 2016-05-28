<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUiSortToMisc extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('care_plan_templates_cpm_miscs', function(Blueprint $table)
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
		Schema::table('care_plan_templates_cpm_miscs', function(Blueprint $table)
		{
			$table->dropColumn('ui_sort');
		});
	}

}
