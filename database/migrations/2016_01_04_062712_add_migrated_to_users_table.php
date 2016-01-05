<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMigratedToUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::connection('mysql_no_prefix')->table('wp_users', function(Blueprint $table)
		{
			if (!Schema::connection('mysql_no_prefix')->hasColumn('wp_users', 'migrated'))
			{
				$table->string('password', 60)->after('program_id');
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
			if (Schema::connection('mysql_no_prefix')->hasColumn('wp_users', 'password')) {
				$table->dropColumn('password');
			}
		});
	}

}
