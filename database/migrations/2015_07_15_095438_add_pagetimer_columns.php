<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPagetimerColumns2 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('page_timer', function($table)
		{
			$table->string('processed', '10')->nullable();
			$table->string('rule_params')->nullable();
			$table->string('rule_found', '10')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('page_timer', function($table)
		{
			$table->dropColumn('processed', 'rule_params', 'rule_found');
		});
	}

}
