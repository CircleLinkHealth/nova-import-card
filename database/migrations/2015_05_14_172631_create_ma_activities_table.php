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
		Schema::create('ma_activities', function(Blueprint $table)
		{
			$table->increments('act_id');
			$table->timestamp('act_date');
			$table->timestamp('act_date_gmt');
			$table->bigInteger('comment_id', false, true);
			$table->integer('sequence_id', false, true)->nullable();
			$table->string('obs_message_id', 30);
			$table->bigInteger('user_id', false, true);
			$table->bigInteger('performed_by', false, true);
			$table->string('act_method', 30);
			$table->string('act_key', 255)->nullable();
			$table->longText('act_value');
			$table->string('act_unit', 255)->nullable();

			$table->index('comment_id', 'comment_id');
			$table->index('act_key', 'meta_key');
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
