<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCpmMedicationGroupsUsers extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cpm_medication_groups_users', function(Blueprint $table)
		{
			$table->increments('id');

			$table->unsignedInteger('patient_id');
			$table->foreign('patient_id')
				->references('id')
				->on((new \App\User())->getTable())
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->unsignedInteger('cpm_medication_group_id');
			$table->foreign('cpm_medication_group_id', 'cpm_med_groups_users_rel_foreign')
				->references('id')
				->on('cpm_medication_groups')
				->onUpdate('cascade')
				->onDelete('cascade');
			
			$table->timestamps();

			$table->unique(['patient_id', 'cpm_medication_group_id'], 'cpm_med_grps_usrs_ptnt_id_cpm_med_grp_id_unique');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('cpm_medication_groups_users');
	}

}
