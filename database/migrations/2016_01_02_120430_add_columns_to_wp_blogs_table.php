<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToWpBlogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::connection('mysql_no_prefix')->table('wp_blogs', function(Blueprint $table)
		{
			if (!Schema::connection('mysql_no_prefix')->hasColumn('wp_blogs', 'att_config')) {
				$table->longText('att_config')->after('lang_id');
				$table->integer('location_id')->after('att_config');
				$table->timestamps();
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
		Schema::connection('mysql_no_prefix')->table('wp_blogs', function(Blueprint $table)
		{
			if (Schema::connection('mysql_no_prefix')->hasColumn('wp_blogs', 'att_config')) {
				$table->dropColumn('att_config');
				$table->dropColumn('location_id');
				$table->dropTimestamps();
				$table->dropSoftDeletes();
			}
		});
	}

}
