<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCpmLifestylesUsers extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cpm_lifestyles_users', function(Blueprint $table)
		{
			$table->increments('id');

			$table->unsignedInteger('patient_id');
			$table->foreign('patient_id')
				->references('id')
				->on((new \App\User())->getTable())
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->unsignedInteger('cpm_lifestyle_id');
			$table->foreign('cpm_lifestyle_id')
				->references('id')
				->on('cpm_lifestyles')
				->onUpdate('cascade')
				->onDelete('cascade');
			
			$table->timestamps();

			$table->unique(['patient_id', 'cpm_lifestyle_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('cpm_lifestyles_users');
	}

}
