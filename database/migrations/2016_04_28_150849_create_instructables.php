<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstructables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('instructables', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('instruction_id');
			$table->unsignedInteger('instructable_id');
			$table->string('instructable_type');
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
		Schema::drop('instructables');
	}

}
