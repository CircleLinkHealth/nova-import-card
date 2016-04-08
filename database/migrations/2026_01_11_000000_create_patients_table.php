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
		$this->down();

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
				$table->string('first_name')->nullable();
				$table->string('last_name')->nullable();
				$table->string('preferred_contact_time')->nullable();
				$table->timestamps();
			});
		}

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

		// seed data
		$users = User::with('meta', 'patient')->get();
		echo 'Users found: '.$users->count().PHP_EOL;
		foreach($users as $user) {
			echo 'Processing user '.$user->ID.PHP_EOL;
			echo 'Rebuild User'.PHP_EOL;
			$user->first_name = $user->firstName;
			$user->last_name = $user->lastName;
			$user->address = $user->address;
			$user->city = $user->city;
			$user->state = $user->state;
			$user->zip = $user->zip;

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
			$user->patient->first_name = $user->firstName;
			$user->patient->last_name = $user->lastName;
			$user->patient->save();

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
