<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRulesActionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('rules_actions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('action_name', 50);
			$table->string('action', 50)->nullable();
			$table->string('action_description', 200)->nullable();
			$table->string('active', 1)->default('Y');
			$table->string('multiple_return', 1)->default('Y');
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
		Schema::drop('rules_actions');
	}

}
