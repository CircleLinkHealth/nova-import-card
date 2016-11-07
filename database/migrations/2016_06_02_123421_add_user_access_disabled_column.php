<?php

use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUserAccessDisabledColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

		echo 'Schema::users adds access_disabled'.PHP_EOL;
		Schema::table('users', function(Blueprint $table)
		{
			if ( ! Schema::hasColumn('users', 'access_disabled')) {
				$table->boolean('access_disabled')->after('status');
			}
		});

		// add approval meta to patient info
		$users = User::withTrashed()->get();
		echo 'Process all users - Users found: '.$users->count().PHP_EOL;
		foreach($users as $user) {
            $user->access_disabled = 0;
            $user->status = 'Active';
            if(!empty($user->deleted_at)) {
                echo 'Processing user: ' . $user->id . PHP_EOL;
                echo 'status: '.$user->deleted_at.PHP_EOL.PHP_EOL;
                $user->user_status = 0;
                $user->status = 'Inactive';
                $user->access_disabled = 1;
            }
            $user->save();
		}

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// no down
	}

}
