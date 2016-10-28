<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUserAutoAttachPrograms  extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if(!Schema::hasColumn('wp_users', 'auto_attach_programs')) {
			Schema::table('wp_users', function(Blueprint $table)
			{
				$table->boolean('auto_attach_programs')->after('user_status');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if(Schema::hasColumn('wp_users', 'auto_attach_programs')) {
			Schema::table('wp_users', function(Blueprint $table)
			{
				$table->dropColumn('auto_attach_programs');
			});
		}
	}

}
