<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRules extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

		Schema::table('rules_intr_conditions', function($table)
		{
			$table->string('value_b')->after('value');
		});

		Schema::table('rules_intr_conditions', function($table)
		{
			$table->dropColumn('action_id');
		});

		Schema::table('rules', function($table)
		{
			$table->string('type_id', 10)->change();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
