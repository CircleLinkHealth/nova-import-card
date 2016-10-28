<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUserIdToParsedCcds extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::connection('mysql_no_prefix')->table('lv_parsed_ccds', function(Blueprint $table)
		{
			if( ! Schema::hasColumn('lv_parsed_ccds', 'user_id' )){
				$table->integer('user_id')->unsigned()->after('ccd');

				$table->foreign('user_id')
                    ->references('id')
					->on('wp_users')
					->onUpdate('cascade')
					->onDelete('cascade');
			}

			if( ! Schema::hasColumn('parsed_ccds', 'deleted_at' )) {
				$table->softDeletes();
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
		Schema::table('parsed_ccds', function(Blueprint $table)
		{
			if( Schema::hasColumn('parsed_ccds', 'user_id' )){
				$table->dropForeign('lv_parsed_ccds_user_id_foreign');

				$table->dropColumn('user_id');
			}

			if( Schema::hasColumn('parsed_ccds', 'deleted_at' )) {
				$table->dropSoftDeletes();
			}
		});
	}

}
