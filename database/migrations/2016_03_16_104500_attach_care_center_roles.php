<?php

use App\Practice;
use App\Role;
use App\User;
use App\UserMeta;
use Illuminate\Database\Migrations\Migration;

class AttachCareCenterRoles  extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// get care-center role
		$careCenterRole = Role::where('name', '=', 'care-center')->first();
		if(!$careCenterRole) {
			$careCenterRole = new Role;
			$careCenterRole->name = 'care-center';
			$careCenterRole->display_name = 'Care Center';
			$careCenterRole->description = 'care center calling center';
			$careCenterRole->save();
		}
		$careCenterRoleId = $careCenterRole->id;
		echo PHP_EOL.' care-center role found id = '.$careCenterRoleId;

		// get all care_center wp users
		$userMetas = UserMeta::where('meta_value', 'LIKE', '%care_center%')->get();
		if($userMetas->count() > 0) {
			$userIds = array();
			// get user ids
			foreach($userMetas as $userMeta) {
				$userIds[] = array('id' => $userMeta->user_id, 'program' => preg_replace("/[^0-9]/","",$userMeta->meta_key));
			}

			// set role for users
			if(!empty($userIds)) {
				foreach ($userIds as $userId => $userInfo) {
					$user = User::find($userInfo['id']);
					if(!$user) {
						continue 1;
					}
					echo PHP_EOL.'Processing '.$userInfo['id'] .'-'. $userInfo['program'];
					// attach role
					if (!$user->roles->contains($careCenterRoleId)) {
						echo PHP_EOL.'Attach '. $userInfo['id'] .' to role '. $careCenterRoleId;
						$user->roles()->attach($careCenterRole);
					}
					echo PHP_EOL.'Attach '. $userInfo['id'] .' to role '. $userInfo['program'];
					// attach program
                    $program = Practice::find($userInfo['program']);
					if(!$program) {
						continue 1;
					}
                    if (!$user->practices->contains($userInfo['program'])) {
						echo PHP_EOL.'Attach '. $userInfo['id'] .' to program '. $userInfo['program'];
                        $user->practices()->attach($program);
					}
					$user->save();
				}
			}
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
	}

}
