<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWpSignupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('wp_signups', function(Blueprint $table)
		{
			$table->bigInteger('signup_id', true);
			$table->string('domain', 200);
			$table->string('path', 100);
			$table->text('title');
			$table->string('user_login', 60);
			$table->string('user_email', 100)->index('user_email');
			$table->dateTime('registered')->default('0000-00-00 00:00:00');
			$table->dateTime('activated')->default('0000-00-00 00:00:00');
			$table->boolean('active')->default(0);
			$table->string('activation_key', 50)->index('activation_key');
			$table->text('meta')->nullable();
			$table->index(['user_login','user_email'], 'user_login_email');
			$table->index(['domain','path'], 'domain_path');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('wp_signups');
	}

}
