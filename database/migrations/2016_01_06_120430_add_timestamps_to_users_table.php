<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTimestampsToUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::connection('mysql_no_prefix')->table('wp_users', function(Blueprint $table)
		{
			if (!Schema::connection('mysql_no_prefix')->hasColumn('wp_users', 'deleted_at')) {
				$table->dropColumn('spam');
				$table->dropColumn('deleted');
				$table->timestamps();
				$table->softDeletes();
			}
		});

		Schema::connection('mysql_no_prefix')->table('wp_users', function(Blueprint $table)
		{
			if (Schema::connection('mysql_no_prefix')->hasColumn('wp_users', 'deleted_at')) {
				DB::connection('mysql_no_prefix')->table('wp_users as u')
					->update(['u.created_at' => DB::raw('u.user_registered')]);
				/*
				$users = User::all()->update(['colour' => 'black']);
				foreach($users as $user) {
					echo $user->id . PHP_EOL;
					$user->created_at = $user->user_registered;
					$user->save();
				}
				*/
			}
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::connection('mysql_no_prefix')->table('wp_users', function(Blueprint $table)
		{
			if (Schema::connection('mysql_no_prefix')->hasColumn('wp_users', 'deleted_at')) {
				$table->string('spam');
				$table->string('deleted');
				$table->dropTimestamps();
				$table->dropSoftDeletes();
			}
		});
	}

}
