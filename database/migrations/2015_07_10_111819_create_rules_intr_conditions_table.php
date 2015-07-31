<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRulesIntrConditionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('rules_intr_conditions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('rule_id');
			$table->unsignedInteger('action_id');
			$table->unsignedInteger('condition_id');
			$table->string('value', 100);
			$table->unsignedInteger('created_by');
			$table->unsignedInteger('modified_by');
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
		Schema::drop('rules_intr_conditions');
	}

}
