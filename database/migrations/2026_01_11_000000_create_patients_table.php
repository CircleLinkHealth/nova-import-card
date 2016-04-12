<?php

use App\User;
use App\PatientInfo;
use App\ProviderInfo;
use App\PhoneNumber;
use App\PatientCarePlan;
use App\PatientCareTeamMember;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
				$table->string('city')->after('address');
				$table->string('state')->after('city');
				$table->string('zip')->after('state');
				$table->string('is_auto_generated')->after('zip');
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
					->references('ID')
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
					->references('ID')
					->on('wp_users')
					->onDelete('cascade')
					->onUpdate('cascade');
				$table->unsignedInteger('ccda_id');
				$table->string('agent_name')->nullable();
				$table->string('agent_telephone')->nullable();
				$table->string('agent_email')->nullable();
				$table->string('agent_relationship')->nullable();
				$table->string('consent_date')->nullable();
				$table->string('gender')->nullable();
				$table->string('mrn_number')->nullable();
				$table->string('preferred_cc_contact_days')->nullable();
				$table->string('preferred_contact_language')->nullable();
				$table->string('preferred_contact_location')->nullable();
				$table->string('preferred_contact_method')->nullable();
				$table->string('preferred_contact_time')->nullable();
				$table->string('preferred_contact_timezone')->nullable();
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
					->references('ID')
					->on('wp_users')
					->onDelete('cascade')
					->onUpdate('cascade');
				$table->unsignedInteger('member_user_id');
				$table->foreign('member_user_id')
					->references('ID')
					->on('wp_users')
					->onDelete('cascade')
					->onUpdate('cascade');
				$table->string('type');
				$table->timestamps();
				$table->softDeletes();
			});
		}

		echo 'Schema::add patient_care_plans'.PHP_EOL;
		if (!Schema::hasTable('patient_care_plans')) {
			Schema::create('patient_care_plans', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('user_id');
				$table->foreign('user_id')
					->references('ID')
					->on('wp_users')
					->onDelete('cascade')
					->onUpdate('cascade');
				$table->unsignedInteger('care_plan_id');
				$table->foreign('care_plan_id')
					->references('ID')
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
				$table->dropColumn('city');
				$table->dropColumn('state');
				$table->dropColumn('zip');
				$table->dropColumn('is_auto_generated');
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
			echo 'Processing user '.$user->ID.PHP_EOL;
			echo 'Rebuild User'.PHP_EOL;
			$user->first_name = $user->getUserMetaByKey('first_name');
			$user->last_name = $user->getUserMetaByKey('last_name');
			$user->address = $user->getUserConfigByKey('address');
			$user->city = $user->getUserConfigByKey('city');
			$user->state = $user->getUserConfigByKey('state');
			$user->zip = $user->getUserConfigByKey('zip');
			$user->save();
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
			echo 'Processing user '.$user->ID.PHP_EOL;
			echo 'Rebuild User->PatientInfo'.PHP_EOL;
			// check if has demographics
			//$patientInfo = PatientInfo::where('user_id', $user->ID)->first();

			// delete existing to reprocess
			if($user->patientInfo) {
				echo 'Removing existing patientInfo'.PHP_EOL;
				$user->patientInfo->delete();
			}

			// create new
			echo 'creating new patientInfo'.PHP_EOL;
			$patientInfo = new PatientInfo;
			$patientInfo->user_id = $user->ID;
			$user->patientInfo()->save($patientInfo);
			$user->load('patientInfo');

			// set values
			$user->patientInfo->agent_name = $user->getUserConfigByKey('agent_name');
			$user->patientInfo->agent_telephone = $user->getUserConfigByKey('agent_telephone');
			$user->patientInfo->agent_email = $user->getUserConfigByKey('agent_email');
			$user->patientInfo->agent_relationship = $user->getUserConfigByKey('agent_relationship');
			$user->patientInfo->consent_date = $user->getUserConfigByKey('consent_date');
			$user->patientInfo->gender = $user->getUserConfigByKey('gender');
			$user->patientInfo->preferred_contact_method = $user->getUserConfigByKey('preferred_contact_method');
			$user->patientInfo->preferred_contact_location = $user->getUserConfigByKey('preferred_contact_location');
			$user->patientInfo->preferred_contact_language = $user->getUserConfigByKey('preferred_contact_language');
			$user->patientInfo->mrn_number = $user->getUserConfigByKey('mrn_number');
			$user->patientInfo->preferred_cc_contact_days = $user->getUserConfigByKey('preferred_cc_contact_days');
			$user->patientInfo->preferred_contact_time = $user->getUserConfigByKey('preferred_contact_time');
			$user->patientInfo->preferred_contact_timezone = $user->getUserConfigByKey('preferred_contact_timezone');
			$user->patientInfo->save();


			// phone numbers
			$phoneNumber = new PhoneNumber;
			$phoneNumber->is_primary = 1;
			$phoneNumber->user_id = $user->ID;
			$phoneNumber->number = $user->getUserConfigByKey('study_phone_number');
			$phoneNumber->save();
			echo PHP_EOL;
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
			echo 'Processing user '.$user->ID.PHP_EOL;
			echo 'Rebuild User->ProviderInfo'.PHP_EOL;
			// check if has demographics
			//$providerInfo = ProviderInfo::where('user_id', $user->ID)->first();

			// delete existing to reprocess
			if($user->providerInfo) {
				echo 'Removing existing providerInfo'.PHP_EOL;
				$user->providerInfo->delete();
			}

			// create new
			echo 'creating new providerInfo'.PHP_EOL;
			$providerInfo = new ProviderInfo;
			$providerInfo->user_id = $user->ID;
			$user->providerInfo()->save($providerInfo);
			$user->load('providerInfo');

			// set values
			$user->providerInfo->prefix = $user->getUserConfigByKey('prefix');
			$user->providerInfo->qualification = $user->getUserConfigByKey('qualification');
			$user->providerInfo->npi_number = $user->getUserConfigByKey('npi_number');
			$user->providerInfo->save();

			echo PHP_EOL;
		}
	}

}
