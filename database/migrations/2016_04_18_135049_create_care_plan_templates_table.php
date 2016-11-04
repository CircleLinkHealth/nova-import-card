<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCarePlanTemplatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('care_plan_templates', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('display_name');
			$table->integer('program_id')->unsigned();
			$table->string('type');
			$table->timestamps();

            $table->foreign('program_id')->references('id')->on('wp_blogs')
				->onDelete('cascade')
				->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('care_plan_templates');
	}

}
