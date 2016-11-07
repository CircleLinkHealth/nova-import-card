<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUserIdToXmlCcdTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::connection('mysql_no_prefix')->table('lv_xml_ccds', function(Blueprint $table)
		{
			if( ! Schema::hasColumn('lv_xml_ccds', 'user_id' )){
				$table->integer('user_id')->unsigned()->after('ccd');

				$table->foreign('user_id')
                    ->references('id')
					  ->on('wp_users')
					  ->onUpdate('cascade')
					  ->onDelete('cascade');
			}

			if( ! Schema::hasColumn('xml_ccds', 'deleted_at' )) {
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
		Schema::table('xml_ccds', function(Blueprint $table)
		{
			if( Schema::hasColumn('xml_ccds', 'user_id' )){
				$table->dropForeign('lv_xml_ccds_user_id_foreign');

				$table->dropColumn('user_id');
			}

			if( Schema::hasColumn('xml_ccds', 'deleted_at' )) {
				$table->dropSoftDeletes();
			}

		});
	}

}
