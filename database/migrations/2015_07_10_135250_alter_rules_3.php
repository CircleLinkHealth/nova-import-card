<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRules3 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('rules_intr_conditions', function($table)
		{
			$table->dropColumn('operator_id');
		});

		Schema::table('rules_intr_conditions', function($table)
		{
			$table->string('operator_id')->after('condition_id');
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
