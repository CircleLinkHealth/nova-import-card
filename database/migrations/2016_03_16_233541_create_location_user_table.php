<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLocationUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('location_user', function(Blueprint $table)
		{
			$table->increments('id');

			//just to be on the safe side check for both locations and lv_locations
			if (Schema::hasTable('lv_locations'))
			{
				$table->unsignedInteger('location_id');
				$table->foreign('location_id')
					->references('id')
					->on('lv_locations')
					->onDelete('cascade')
					->onUpdate('cascade');
			}

			if (Schema::hasTable('locations'))
			{
				$table->unsignedInteger('location_id');
				$table->foreign('location_id')
					->references('id')
					->on('locations')
					->onDelete('cascade')
					->onUpdate('cascade');
			}

			$table->unsignedInteger('user_id');
			$table->foreign('user_id')
                ->references('id')
				->on('wp_users')
				->onDelete('cascade')
				->onUpdate('cascade');

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
		Schema::drop('location_user');
	}

}
