<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddNameColumnsToWpBlogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::connection('mysql_no_prefix')->table('wp_blogs', function(Blueprint $table)
		{
			if (!Schema::connection('mysql_no_prefix')->hasColumn('wp_blogs', 'name')) {
                $table->string('name', 100)->after('id');
				$table->string('display_name')->nullable()->after('name');
				$table->string('short_display_name')->nullable()->after('display_name');
				$table->string('description')->nullable()->after('short_display_name');
			}

		});

		Schema::connection('mysql_no_prefix')->table('wp_blogs', function(Blueprint $table)
		{
			if (Schema::connection('mysql_no_prefix')->hasColumn('wp_blogs', 'name')) {
				DB::connection('mysql_no_prefix')->table('wp_blogs as u')
                    ->update(['u.name' => DB::raw('u.id')]);
				DB::connection('mysql_no_prefix')->table('wp_blogs as u')
					->update(['u.created_at' => DB::raw('u.registered')]);
			}

		});

		Schema::connection('mysql_no_prefix')->table('wp_blogs', function(Blueprint $table)
		{
			if (Schema::connection('mysql_no_prefix')->hasColumn('wp_blogs', 'name')) {
				$table->unique('name');
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
			if (Schema::connection('mysql_no_prefix')->hasColumn('wp_blogs', 'name')) {
				$table->dropColumn('name');
				$table->dropColumn('display_name')->nullable();
				$table->dropColumn('short_display_name')->nullable();
				$table->dropColumn('description')->nullable();
			}
		});
	}

}
