<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRulesOperatorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('rules_operators', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('operator', 50);
			$table->string('operator_description', 200)->nullable();
			$table->string('operation', 200);
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
		Schema::drop('rules_operators');
	}

}
