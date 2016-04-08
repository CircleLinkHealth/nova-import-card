<?php

use App\User;
use App\Patient;
use App\Provider;
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

		// first run this down, clean slate each run
		echo 'Running down()'.PHP_EOL;
		$this->down();

		echo 'Schema::wp_users updates'.PHP_EOL;
		Schema::table('wp_users', function(Blueprint $table)
		{
			if ( ! Schema::hasColumn('wp_users', 'first_name')) {
				$table->string('first_name');
				$table->string('last_name');
				$table->string('address');
				$table->string('city');
				$table->string('state');
				$table->string('zip');
				$table->string('is_auto_generated');
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
			});
		}

		echo 'Schema::add providers'.PHP_EOL;
		if (!Schema::hasTable('providers')) {
			Schema::create('providers', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('user_id');
				$table->foreign('user_id')
					->references('ID')
					->on('wp_users')
					->onDelete('cascade')
					->onUpdate('cascade');
				$table->string('qualification')->nullable();
				$table->string('npi_number')->nullable();
				$table->string('specialty')->nullable();
				$table->timestamps();
			});
		}

		echo 'Schema::add patients'.PHP_EOL;
		if (!Schema::hasTable('patients')) {
			Schema::create('patients', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('user_id');
				$table->foreign('user_id')
					->references('ID')
					->on('wp_users')
					->onDelete('cascade')
					->onUpdate('cascade');
				$table->unsignedInteger('ccda_id');
				$table->string('mrn_number')->nullable();
				$table->string('preferred_contact_time')->nullable();
				$table->timestamps();
			});
		}

		echo 'Schema::add patient_care_team_providers'.PHP_EOL;
		if (!Schema::hasTable('patient_care_team_providers')) {
			Schema::create('patient_care_team_providers', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('user_id');
				$table->unsignedInteger('provider_id');
				$table->foreign('user_id')
					->references('ID')
					->on('wp_users')
					->onDelete('cascade')
					->onUpdate('cascade');
				$table->string('type');
				$table->timestamps();
			});
		}

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

		// seed data patients
		$users = User::whereHas('roles', function ($q) {
			$q->where('name', '=', 'participant');
		})->with('meta', 'patient')->get();
		echo 'Process role patient users - Users found: '.$users->count().PHP_EOL;
		foreach($users as $user) {
			echo 'Processing user '.$user->ID.PHP_EOL;
			echo 'Rebuild User->Patient'.PHP_EOL;
			// check if has demographics
			$patientInfo = Patient::where('user_id', $user->ID)->first();

			// delete existing to reprocess
			if($user->patient) {
				echo 'Removing existing patient'.PHP_EOL;
				$user->patient->delete();
			}

			// create new
			echo 'creating new patient'.PHP_EOL;
			$patientInfo = new Patient;
			$patientInfo->user_id = $user->ID;
			$user->patient()->save($patientInfo);
			$user->load('patient');

			// set values
			$user->patient->preferred_contact_time = $user->getUserConfigByKey('preferred_contact_time');
			$user->patient->mrn_number = $user->getUserConfigByKey('mrn_number');
			$user->patient->save();

			echo PHP_EOL;
		}



		// seed data providers
		$users = User::whereHas('roles', function ($q) {
			$q->where('name', '=', 'provider');
		})->with('meta', 'provider')->get();
		echo 'Process role provider users - Users found: '.$users->count().PHP_EOL;
		foreach($users as $user) {
			echo 'Processing user '.$user->ID.PHP_EOL;
			echo 'Rebuild User->Provider'.PHP_EOL;
			// check if has demographics
			$providerInfo = Provider::where('user_id', $user->ID)->first();

			// delete existing to reprocess
			if($user->provider) {
				echo 'Removing existing provider'.PHP_EOL;
				$user->provider->delete();
			}

			// create new
			echo 'creating new provider'.PHP_EOL;
			$providerInfo = new Provider;
			$providerInfo->user_id = $user->ID;
			$user->provider()->save($providerInfo);
			$user->load('provider');

			// set values
			$user->provider->qualification = $user->qualification;
			$user->provider->npi_number = $user->npiNumber;
			$user->provider->save();

			echo PHP_EOL;
		}
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
			}
		});

		if (Schema::hasTable('patients')) {
			Schema::drop('patients');
		}

		if (Schema::hasTable('patient_care_team_providers')) {
			Schema::drop('patient_care_team_providers');
		}

		if (Schema::hasTable('providers')) {
			Schema::drop('providers');
		}

		if (Schema::hasTable('phone_numbers')) {
			Schema::drop('phone_numbers');
		}
	}

}
