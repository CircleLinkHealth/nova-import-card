<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToEligibilityBatchesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('eligibility_batches', function(Blueprint $table)
		{
			$table->foreign('initiator_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('practice_id')->references('id')->on('practices')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('eligibility_batches', function(Blueprint $table)
		{
			$table->dropForeign('eligibility_batches_initiator_id_foreign');
			$table->dropForeign('eligibility_batches_practice_id_foreign');
		});
	}

}
