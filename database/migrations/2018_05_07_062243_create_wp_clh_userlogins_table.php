<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWpClhUserloginsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('wp_clh_userlogins', function(Blueprint $table)
		{
			$table->bigInteger('login_ID', true);
			$table->text('login_username');
			$table->timestamp('login_date')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->bigInteger('login_status')->default(1);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('wp_clh_userlogins');
	}

}
