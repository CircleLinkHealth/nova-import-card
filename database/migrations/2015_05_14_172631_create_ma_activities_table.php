<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMaActivitiesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('activities', function(Blueprint $table)
		{
			$table->increments('id');

			$table->string('type', 255)->nullable();
			$table->unsignedInteger('duration');
			$table->string('duration_unit', 30)->nullable();

			$table->unsignedInteger('patient_id');
			$table->unsignedInteger('provider_id');
			$table->unsignedInteger('logger_id');

			$table->unsignedInteger('comment_id');
			$table->unsignedInteger('sequence_id')->nullable();
			$table->string('obs_message_id', 30);

			$table->string('logged_from', 30);

			$table->timestamp('performed_at');
			$table->timestamp('performed_at_gmt');
			$table->timestamps();
			$table->softDeletes();


			$table->index('comment_id', 'comment_id');
			$table->index('type', 'type');
			$table->index('obs_message_id', 'obs_message_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('ma_activities');
	}

}
