<?php

use App\Practice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeUsersTableAndRemoveMaPrefixTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		echo 'Schema::wp_users change to users'.PHP_EOL;
		Schema::table('wp_users', function(Blueprint $table)
		{
			if ( Schema::hasTable('wp_users')) {
				Schema::rename('wp_users', 'users');
			}

			if ( Schema::hasTable('wp_usermeta')) {
				Schema::rename('wp_usermeta', 'usermeta');
			}
		});

        $programs = Practice::all();
		foreach($programs as $program) {
			// remove tables
            Schema::dropIfExists('ma_' . $program->id . '_observations');
            echo PHP_EOL . 'removed ma_' . $program->id . '_observations';
            Schema::dropIfExists('ma_' . $program->id . '_observationmeta');
            echo PHP_EOL . 'removed ma_' . $program->id . '_observationmeta';
		}

		Schema::dropIfExists('wp_userconfig');
		echo PHP_EOL.'removed wp_userconfig';

		Schema::dropIfExists('wp_userconfig');
		echo PHP_EOL.'removed wp_userconfig';

		Schema::dropIfExists('wp_wfBlockedIPLog');
		echo PHP_EOL.'removed wp_wfBlockedIPLog';

		Schema::dropIfExists('wp_wfBlocks');
		echo PHP_EOL.'removed wp_wfBlocks';

		Schema::dropIfExists('wp_wfBlocksAdv');
		echo PHP_EOL.'removed wp_wfBlocksAdv';

		Schema::dropIfExists('wp_wfConfig');
		echo PHP_EOL.'removed wp_wfConfig';

		Schema::dropIfExists('wp_wfCrawlers');
		echo PHP_EOL.'removed wp_wfCrawlers';

		Schema::dropIfExists('wp_wfFileMods');
		echo PHP_EOL.'removed wp_wfFileMods';

		Schema::dropIfExists('wp_wfHits');
		echo PHP_EOL.'removed wp_wfHits';

		Schema::dropIfExists('wp_wfHoover');
		echo PHP_EOL.'removed wp_wfHoover';

		Schema::dropIfExists('wp_wfIssues');
		echo PHP_EOL.'removed wp_wfIssues';

		Schema::dropIfExists('wp_wfLeechers');
		echo PHP_EOL.'removed wp_wfLeechers';

		Schema::dropIfExists('wp_wfLockedOut');
		echo PHP_EOL.'removed wp_wfLockedOut';

		Schema::dropIfExists('wp_wfLocs');
		echo PHP_EOL.'removed wp_wfLocs';

		Schema::dropIfExists('wp_wfLogins');
		echo PHP_EOL.'removed wp_wfLogins';

		Schema::dropIfExists('wp_wfNet404s');
		echo PHP_EOL.'removed wp_wfNet404s';

		Schema::dropIfExists('wp_wfReverseCache');
		echo PHP_EOL.'removed wp_wfReverseCache';

		Schema::dropIfExists('wp_wfScanners');
		echo PHP_EOL.'removed wp_wfScanners';

		Schema::dropIfExists('wp_wfStatus');
		echo PHP_EOL.'removed wp_wfStatus';

		Schema::dropIfExists('wp_wfThrottleLog');
		echo PHP_EOL.'removed wp_wfThrottleLog';

		Schema::dropIfExists('wp_wfVulnScanners');
		echo PHP_EOL.'removed wp_wfVulnScanners';

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{

        if ( Schema::hasTable('users')) {
            Schema::rename('users', 'wp_users');
        }

        if ( Schema::hasTable('usermeta')) {
            Schema::rename('usermeta', 'wp_usermeta');
        }

	}

}
