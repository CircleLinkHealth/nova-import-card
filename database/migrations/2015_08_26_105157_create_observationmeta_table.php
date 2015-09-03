<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateObservationmetaTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('observationmeta', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('obs_id');
			$table->unsignedInteger('comment_id');
			$table->string('message_id', 30);
			$table->string('meta_key', 50);
			$table->string('meta_value');
			$table->unsignedInteger('program_id');
			$table->unsignedInteger('legacy_meta_id');
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
		Schema::drop('lv_observations');
	}

}
