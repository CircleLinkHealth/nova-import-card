<?php

use App\PatientCareTeamMember;
use App\User;
use Illuminate\Database\Migrations\Migration;

class CareTeamMemberMeta extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// move care_team meta into patient_care_team_members table
		$users = User::whereHas('roles', function ($q) {
			$q->where('name', '=', 'participant');
		})->with('meta', 'patientInfo')->get();
		echo 'Process role patient users - Users found: '.$users->count().PHP_EOL;
		foreach($users as $user) {
            echo 'Processing user ' . $user->id . PHP_EOL;
			echo 'Deleting existing care team members'.PHP_EOL;
			$user->patientCareTeamMembers()->delete();
			echo 'Migrate Care Team Members from Meta'.PHP_EOL;
			// care team
			$careTeam = $user->getUserConfigByKey('care_team');
			if(!empty($careTeam)) {
				if(is_array($careTeam)) {
					foreach($careTeam as $ct) {
						if(is_numeric($ct)) {
							$careTeamMember = new PatientCareTeamMember;
                            $careTeamMember->user_id = $user->id;
							$careTeamMember->member_user_id = $ct;
							$careTeamMember->type = 'member';
							$careTeamMember->save();
							echo 'added member' . PHP_EOL;
						} else {
							echo 'member not int = '.$ct.''. PHP_EOL;
						}
					}
				} else {
					if(is_numeric($careTeam)) {
						$careTeamMember = new PatientCareTeamMember;
                        $careTeamMember->user_id = $user->id;
						$careTeamMember->member_user_id = $careTeam;
						$careTeamMember->type = 'member';
						$careTeamMember->save();
						echo 'added member' . PHP_EOL;
					}
				}
			}

			// care team billing provider
			$careTeamBP = $user->getUserConfigByKey('billing_provider');
			if(!empty($careTeamBP) && is_numeric($careTeamBP)) {
				$careTeamMember = new PatientCareTeamMember;
                $careTeamMember->user_id = $user->id;
				$careTeamMember->member_user_id = $careTeamBP;
				$careTeamMember->type = 'billing_provider';
				$careTeamMember->save();
				echo 'added billing_provider' . PHP_EOL;
			} else {
				echo 'billing_provider not int = '.$careTeamBP.''. PHP_EOL;
			}

			// care team lead contacts
			$careTeamLC = $user->getUserConfigByKey('lead_contact');
			if(!empty($careTeamLC) && is_numeric($careTeamLC)) {
				$careTeamMember = new PatientCareTeamMember;
                $careTeamMember->user_id = $user->id;
				$careTeamMember->member_user_id = $careTeamLC;
				$careTeamMember->type = 'lead_contact';
				$careTeamMember->save();
				echo 'added lead_contact'.PHP_EOL;
			} else {
				echo 'lead_contact not int = '.$careTeamLC.''. PHP_EOL;
			}

			// care team send alert to
			$careTeamSA = $user->getUserConfigByKey('send_alert_to');
			if(!empty($careTeamSA)) {
				if(is_array($careTeamSA)) {
					foreach($careTeamSA as $sa) {
						if(is_numeric($sa)) {
							$careTeamMember = new PatientCareTeamMember;
                            $careTeamMember->user_id = $user->id;
							$careTeamMember->member_user_id = $sa;
							$careTeamMember->type = 'send_alert_to';
							$careTeamMember->save();
							echo 'added send_alert_to' . PHP_EOL;
						} else {
							echo 'send_alert_to not int = '.$sa.''. PHP_EOL;
						}
					}
				} else {
					if(is_numeric($careTeamSA)) {
						$careTeamMember = new PatientCareTeamMember;
                        $careTeamMember->user_id = $user->id;
						$careTeamMember->member_user_id = $careTeamSA;
						$careTeamMember->type = 'send_alert_to';
						$careTeamMember->save();
						echo 'added send_alert_to' . PHP_EOL;
					}
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
