<?php

use App\PatientCareTeamMember;
use App\PatientInfo;
use App\PhoneNumber;
use App\ProviderInfo;
use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePatientsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

		/* TEMP TEST CODE
		$user1210 = User::find(1210);
		if($user1210) {
			$user1210->delete();
			echo '1210 deleted ' . PHP_EOL;
		}
		$user1210 = User::withTrashed()->find(1210);
		$user1210->restore();
		echo '1210 restored '.PHP_EOL;
		dd();
		*/

		// first run this down, clean slate each run
		echo 'Running down()'.PHP_EOL;
		$this->down();

		echo 'Schema::wp_users updates'.PHP_EOL;
		Schema::table('wp_users', function(Blueprint $table)
		{
			if ( ! Schema::hasColumn('wp_users', 'first_name')) {
				$table->string('first_name')->after('display_name');
				$table->string('last_name')->after('first_name');
				$table->string('address')->after('last_name');
				$table->string('address2')->after('address');
				$table->string('city')->after('address2');
				$table->string('state')->after('city');
				$table->string('zip')->after('state');
				$table->string('status')->after('zip');
				$table->boolean('is_auto_generated')->after('status');
				$table->dropColumn('deleted');
			}
		});

		echo 'Schema::add phone_numbers'.PHP_EOL;
		if (!Schema::hasTable('phone_numbers')) {
			Schema::create('phone_numbers', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('user_id');
				$table->unsignedInteger('location_id');
				$table->string('number')->nullable();
				$table->string('type')->nullable();
				$table->boolean('is_primary');
				$table->timestamps();
				$table->softDeletes();
			});
		}

		echo 'Schema::add provider_info'.PHP_EOL;
		if (!Schema::hasTable('provider_info')) {
			Schema::create('provider_info', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('user_id');
				$table->foreign('user_id')
                    ->references('id')
					->on('wp_users')
					->onDelete('cascade')
					->onUpdate('cascade');
				$table->string('prefix')->nullable();
				$table->string('qualification')->nullable();
				$table->string('npi_number')->nullable();
				$table->string('specialty')->nullable();
				$table->timestamps();
				$table->softDeletes();
			});
		}

		echo 'Schema::add patient_info'.PHP_EOL;
		if (!Schema::hasTable('patient_info')) {
			Schema::create('patient_info', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('user_id');
				$table->foreign('user_id')
                    ->references('id')
					->on('wp_users')
					->onDelete('cascade')
					->onUpdate('cascade');
				$table->unsignedInteger('ccda_id');
				$table->string('active_date')->nullable();
				$table->string('agent_name')->nullable();
				$table->string('agent_telephone')->nullable();
				$table->string('agent_email')->nullable();
				$table->string('agent_relationship')->nullable();
				$table->string('birth_date')->nullable();
				$table->string('ccm_status')->nullable();
				$table->string('consent_date')->nullable();
				$table->string('cur_month_activity_time')->nullable();
				$table->string('gender')->nullable();
				$table->string('date_paused')->nullable();
				$table->string('date_withdrawn')->nullable();
				$table->string('mrn_number')->nullable();
				$table->string('preferred_cc_contact_days')->nullable();
				$table->string('preferred_contact_language')->nullable();
				$table->string('preferred_contact_location')->nullable();
				$table->string('preferred_contact_method')->nullable();
				$table->string('preferred_contact_time')->nullable();
				$table->string('preferred_contact_timezone')->nullable();
				$table->string('registration_date')->nullable();
				$table->string('daily_reminder_optin')->nullable();
				$table->string('daily_reminder_time')->nullable();
				$table->string('daily_reminder_areas')->nullable();
				$table->string('hospital_reminder_optin')->nullable();
				$table->string('hospital_reminder_time')->nullable();
				$table->string('hospital_reminder_areas')->nullable();

				$table->timestamps();
				$table->softDeletes();
			});
		}

		echo 'Schema::add patient_care_team_members'.PHP_EOL;
		if (!Schema::hasTable('patient_care_team_members')) {
			Schema::create('patient_care_team_members', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('user_id');
				$table->foreign('user_id')
                    ->references('id')
					->on('wp_users')
					->onDelete('cascade')
					->onUpdate('cascade');
				$table->unsignedInteger('member_user_id');
				$table->foreign('member_user_id')
                    ->references('id')
					->on('wp_users')
					->onDelete('cascade')
					->onUpdate('cascade');
				$table->string('type');
				$table->timestamps();
			});
		}

		echo 'Schema::add patient_care_plans'.PHP_EOL;
		if (!Schema::hasTable('patient_care_plans')) {
			Schema::create('patient_care_plans', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('user_id');
				$table->foreign('user_id')
                    ->references('id')
					->on('wp_users')
					->onDelete('cascade')
					->onUpdate('cascade');
				$table->unsignedInteger('care_plan_id');
				$table->foreign('care_plan_id')
                    ->references('id')
					->on('care_plans')
					->onDelete('cascade')
					->onUpdate('cascade');
				$table->string('qa_approver');
				$table->string('qa_date');
				$table->string('provider_approver');
				$table->string('provider_date');
				$table->string('status');
				$table->timestamps();
				$table->softDeletes();
			});
		}

		$this->migrateUserInfo();
		$this->migratePatientInfo();
		$this->migrateProviderInfo();




	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{

		Schema::table('wp_users', function(Blueprint $table)
		{
			if ( Schema::hasColumn('wp_users', 'first_name')) {
				$table->dropColumn('first_name');
				$table->dropColumn('last_name');
				$table->dropColumn('address');
				$table->dropColumn('address2');
				$table->dropColumn('city');
				$table->dropColumn('state');
				$table->dropColumn('zip');
				$table->dropColumn('is_auto_generated');
				$table->dropColumn('status');
				$table->boolean('deleted');
			}
		});

		if (Schema::hasTable('phone_numbers')) {
			Schema::drop('phone_numbers');
		}

		if (Schema::hasTable('provider_info')) {
			Schema::drop('provider_info');
		}

		if (Schema::hasTable('patient_info')) {
			Schema::drop('patient_info');
		}

		if (Schema::hasTable('patient_care_plans')) {
			Schema::drop('patient_care_plans');
		}

		if (Schema::hasTable('patient_care_team_members')) {
			Schema::drop('patient_care_team_members');
		}
	}





	public function migrateUserInfo()
	{
		// seed data user demographics
		$users = User::with('meta')->get();
		echo 'Process all role users demographics - Users found: '.$users->count().PHP_EOL;
		foreach($users as $user) {
            echo 'Processing user ' . $user->id . PHP_EOL;
			echo 'Rebuild User'.PHP_EOL;
			$user->first_name = $user->getUserMetaByKey('first_name');
			$user->last_name = $user->getUserMetaByKey('last_name');
			$user->address = $user->getUserConfigByKey('address');
			$user->address = $user->getUserConfigByKey('address2');
			$user->city = $user->getUserConfigByKey('city');
			$user->state = $user->getUserConfigByKey('state');
			$user->zip = $user->getUserConfigByKey('zip');
			$user->zip = $user->getUserConfigByKey('status');
			$user->save();

			// phone numbers
			if(!empty($user->getUserConfigByKey('study_phone_number'))) {
				$phoneNumber = new PhoneNumber;
				$phoneNumber->is_primary = 1;
                $phoneNumber->user_id = $user->id;
				$phoneNumber->number = $user->getUserConfigByKey('study_phone_number');
				$phoneNumber->type = 'home';
				$phoneNumber->save();
				echo 'Added home study_phone_number'.PHP_EOL;
			}
			if(!empty($user->getUserConfigByKey('work_phone_number'))) {
				$phoneNumber = new PhoneNumber;
                $phoneNumber->user_id = $user->id;
				$phoneNumber->number = $user->getUserConfigByKey('work_phone_number');
				$phoneNumber->type = 'work';
				$phoneNumber->save();
				echo 'Added work work_phone_number'.PHP_EOL;
			}
			if(!empty($user->getUserConfigByKey('mobile_phone_number'))) {
				$phoneNumber = new PhoneNumber;
                $phoneNumber->user_id = $user->id;
				$phoneNumber->number = $user->getUserConfigByKey('mobile_phone_number');
				$phoneNumber->type = 'mobile';
				$phoneNumber->save();
				echo 'Added mobile mobile_phone_number'.PHP_EOL;
			}

			echo 'Saved '.PHP_EOL;
		}
	}


	public function migratePatientInfo()
	{

		// seed data patients
		$users = User::whereHas('roles', function ($q) {
			$q->where('name', '=', 'participant');
		})->with('meta', 'patientInfo')->get();
		echo 'Process role patient users - Users found: '.$users->count().PHP_EOL;
		foreach($users as $user) {
            echo 'Processing user ' . $user->id . PHP_EOL;
			echo 'Rebuild User->PatientInfo'.PHP_EOL;
			// check if has demographics
            //$patientInfo = PatientInfo::where('user_id', $user->id)->first();

			// delete existing to reprocess
			if($user->patientInfo) {
				echo 'Removing existing patientInfo'.PHP_EOL;
				$user->patientInfo->delete();
			}

			// create new
			echo 'creating new patientInfo'.PHP_EOL;
			$patientInfo = new PatientInfo;
            $patientInfo->user_id = $user->id;
			$user->patientInfo()->save($patientInfo);
			$user->load('patientInfo');

			// set values
			$user->patientInfo->active_date = $user->getUserConfigByKey('active_date');
			$user->patientInfo->agent_name = $user->getUserConfigByKey('agent_name');
			$user->patientInfo->agent_telephone = $user->getUserConfigByKey('agent_telephone');
			$user->patientInfo->agent_email = $user->getUserConfigByKey('agent_email');
			$user->patientInfo->agent_relationship = $user->getUserConfigByKey('agent_relationship');
			$user->patientInfo->birth_date = $user->getUserConfigByKey('birth_date');
			$user->patientInfo->ccm_status = $user->getUserConfigByKey('ccm_status');
			$user->patientInfo->consent_date = $user->getUserConfigByKey('consent_date');
			$user->patientInfo->cur_month_activity_time = $user->getUserConfigByKey('cur_month_activity_time');
			$user->patientInfo->date_paused = $user->getUserConfigByKey('date_paused');
			$user->patientInfo->date_withdrawn = $user->getUserConfigByKey('date_withdrawn');
			$user->patientInfo->gender = $user->getUserConfigByKey('gender');
			$user->patientInfo->preferred_contact_method = $user->getUserConfigByKey('preferred_contact_method');
			$user->patientInfo->preferred_contact_location = $user->getUserConfigByKey('preferred_contact_location');
			$user->patientInfo->preferred_contact_language = $user->getUserConfigByKey('preferred_contact_language');
			$user->patientInfo->mrn_number = $user->getUserConfigByKey('mrn_number');
			$user->patientInfo->preferred_cc_contact_days = $user->getUserConfigByKey('preferred_cc_contact_days');
			$user->patientInfo->preferred_contact_time = $user->getUserConfigByKey('preferred_contact_time');
			$user->patientInfo->preferred_contact_timezone = $user->getUserConfigByKey('preferred_contact_timezone');
			$user->patientInfo->registration_date = $user->getUserConfigByKey('registration_date');
			$user->patientInfo->daily_reminder_optin = $user->getUserConfigByKey('daily_reminder_optin');
			$user->patientInfo->daily_reminder_time = $user->getUserConfigByKey('daily_reminder_time');
			$user->patientInfo->daily_reminder_areas = $user->getUserConfigByKey('daily_reminder_areas');
			$user->patientInfo->hospital_reminder_optin = $user->getUserConfigByKey('hospital_reminder_optin');
			$user->patientInfo->hospital_reminder_time = $user->getUserConfigByKey('hospital_reminder_time');
			$user->patientInfo->hospital_reminder_areas = $user->getUserConfigByKey('hospital_reminder_areas');

			$user->patientInfo->save();

			// care team
			$careTeam = $user->getUserConfigByKey('care_team');
			if(!empty($careTeam)) {
				if(is_array($careTeam)) {
				  // do nothing, these will fall under bp, lc, or sa
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

	public function migrateProviderInfo()
	{
		// seed data provider_info
		$users = User::whereHas('roles', function ($q) {
			$q->where('name', '=', 'provider');
		})->with('meta', 'providerInfo')->get();
		echo 'Process role provider users providerInfo - Users found: '.$users->count().PHP_EOL;
		foreach($users as $user) {
            echo 'Processing user ' . $user->id . PHP_EOL;
			echo 'Rebuild User->ProviderInfo'.PHP_EOL;
			// check if has demographics
            //$providerInfo = ProviderInfo::where('user_id', $user->id)->first();

			// delete existing to reprocess
			if($user->providerInfo) {
				echo 'Removing existing providerInfo'.PHP_EOL;
				$user->providerInfo->delete();
			}

			// create new
			echo 'creating new providerInfo'.PHP_EOL;
			$providerInfo = new ProviderInfo;
            $providerInfo->user_id = $user->id;
			$user->providerInfo()->save($providerInfo);
			$user->load('providerInfo');

			// set values
			$user->providerInfo->npi_number = $user->getUserConfigByKey('npi_number');
			$user->providerInfo->prefix = $user->getUserConfigByKey('prefix');
			$user->providerInfo->specialty = $user->getUserConfigByKey('specialty');
			$user->providerInfo->qualification = $user->getUserConfigByKey('qualification');
			$user->providerInfo->save();

			echo PHP_EOL;
		}
	}

}
