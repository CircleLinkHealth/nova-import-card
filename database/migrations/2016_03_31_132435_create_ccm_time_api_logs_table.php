<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCcmTimeApiLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ccm_time_api_logs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('activity_id');
			$table->foreign('activity_id', 'activity_id_foreign')
				->references('id')
				->on((new \App\Activity())->getTable())
				->onUpdate('cascade')
				->onDelete('cascade');
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
		Schema::drop('ccm_time_api_logs');
	}

}
