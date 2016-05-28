<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFkToNullableCpt extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table((new \App\CarePlanTemplate())->getTable(), function(Blueprint $table){
			$table->unsignedInteger('program_id')->nullable()->change();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table((new \App\CarePlanTemplate())->getTable(), function(Blueprint $table){

		});
	}

}
