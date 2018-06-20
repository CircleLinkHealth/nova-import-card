<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCpmLifestylesUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('cpm_lifestyles_users', function(Blueprint $table)
		{
			$table->foreign('cpm_instruction_id')->references('id')->on('cpm_instructions')->onUpdate('CASCADE')->onDelete('SET NULL');
			$table->foreign('cpm_lifestyle_id')->references('id')->on('cpm_lifestyles')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('patient_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('cpm_lifestyles_users', function(Blueprint $table)
		{
			$table->dropForeign('cpm_lifestyles_users_cpm_instruction_id_foreign');
			$table->dropForeign('cpm_lifestyles_users_cpm_lifestyle_id_foreign');
			$table->dropForeign('cpm_lifestyles_users_patient_id_foreign');
		});
	}

}
