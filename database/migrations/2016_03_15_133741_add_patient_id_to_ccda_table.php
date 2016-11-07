<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPatientIdToCcdaTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ccdas', function(Blueprint $table)
		{
			$table->unsignedInteger('patient_id')->after('user_id')->nullable();
			$table->foreign('patient_id', 'users_patient_id_foreign')
                ->references('id')
				->on('wp_users')
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
		Schema::table('ccdas', function(Blueprint $table)
		{
			$table->dropForeign('users_patient_id_foreign');
			$table->dropColumn('patient_id');
		});
	}

}
