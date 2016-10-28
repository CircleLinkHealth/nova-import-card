<?php

use App\Practice;
use Illuminate\Database\Migrations\Migration;

class RemoveWpPrefixedTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        $programs = Practice::all();
		foreach($programs as $program) {
			$name = str_replace(".careplanmanager.com","",$program->domain);
            echo PHP_EOL . 'processing program:: ' . $name . '(' . $program->id . ')';
            $existingProgram = Practice::where('name', '=', $name)->first();
			if(!$existingProgram) {
				$program->name = $name;
				$program->display_name = ucfirst($name);
				$program->save();
			}

			// update program location id
            if (Schema::hasTable('wp_' . $program->id . '_options')) {
                $location = DB::connection('mysql_no_prefix')->table('wp_' . $program->id . '_options')->where('option_name',
                    'location_id')->first();
				if($location && !empty($location->option_value)) {
					echo PHP_EOL.'set wp_blogs.location_id to '. $location->option_value;
					$program->location_id = $location->option_value;
					$program->save();
				}
			}

			// remove tables
            echo PHP_EOL . 'removing all wp_' . $program->id . '_* tables';
            Schema::dropIfExists('wp_' . $program->id . '_clh_userlogins');
            Schema::dropIfExists('wp_' . $program->id . '_commentmeta');
            Schema::dropIfExists('wp_' . $program->id . '_comments');
            Schema::dropIfExists('wp_' . $program->id . '_links');
            Schema::dropIfExists('wp_' . $program->id . '_options');
            Schema::dropIfExists('wp_' . $program->id . '_postmeta');
            Schema::dropIfExists('wp_' . $program->id . '_posts');
            Schema::dropIfExists('wp_' . $program->id . '_term_relationships');
            Schema::dropIfExists('wp_' . $program->id . '_terms');
            Schema::dropIfExists('wp_' . $program->id . '_term_taxonomy');
            Schema::dropIfExists('ma_' . $program->id . '_outbound_log');
		}
		echo PHP_EOL.'completed, continued to next migration else finished....'.PHP_EOL.PHP_EOL;
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// cant reverse this
	}

}
