<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateObservationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('observations', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamp('obs_date');
			$table->timestamp('obs_date_gmt');
			$table->unsignedInteger('comment_id');
			$table->unsignedInteger('sequence_id');
			$table->string('obs_message_id', 30);
			$table->unsignedInteger('user_id');
			$table->string('obs_method', 30);
			$table->string('obs_key', 30);
			$table->string('obs_value', 30);
			$table->string('obs_unit', 30);
			$table->unsignedInteger('program_id');
			$table->unsignedInteger('legacy_obs_id');
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
