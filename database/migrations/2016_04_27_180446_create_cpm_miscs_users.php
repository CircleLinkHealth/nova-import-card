<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCpmMiscsUsers extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cpm_miscs_users', function(Blueprint $table)
		{
			$table->increments('id');

			$table->unsignedInteger('patient_id');
			$table->foreign('patient_id')
				->references('id')
				->on((new \App\User())->getTable())
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->unsignedInteger('cpm_misc_id');
			$table->foreign('cpm_misc_id')
				->references('id')
				->on((new \App\Models\CPM\CpmMisc())->getTable())
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->timestamps();

			$table->unique(['patient_id', 'cpm_misc_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('cpm_miscs_users');
	}

}
