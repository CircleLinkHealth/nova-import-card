<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToNurseCareRateLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('nurse_care_rate_logs', function(Blueprint $table)
		{
			$table->foreign('activity_id')->references('id')->on('lv_activities')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('nurse_id')->references('id')->on('nurse_info')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('nurse_care_rate_logs', function(Blueprint $table)
		{
			$table->dropForeign('nurse_care_rate_logs_activity_id_foreign');
			$table->dropForeign('nurse_care_rate_logs_nurse_id_foreign');
		});
	}

}
