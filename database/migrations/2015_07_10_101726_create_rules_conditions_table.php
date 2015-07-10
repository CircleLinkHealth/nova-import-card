<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRulesConditionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('rules_conditions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('condition_name', 50);
			$table->string('condition', 50)->nullable();
			$table->string('condition_description', 200)->nullable();
			$table->string('active', 100)->default('Y');
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
		Schema::drop('rules_conditions');
	}

}
