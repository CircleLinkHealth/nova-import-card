<?php

use App\User;
use App\UserPatientInfo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserDemographicsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

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

		if (!Schema::hasTable('user_patient_info')) {
			Schema::create('user_patient_info', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('user_id');
				$table->foreign('user_id')
					->references('ID')
					->on('wp_users')
					->onDelete('cascade')
					->onUpdate('cascade');
				$table->string('first_name')->nullable();
				$table->string('last_name')->nullable();
				$table->string('preferred_contact_time')->nullable();
				$table->timestamps();
			});
		}
		// seed data
		$users = User::with('meta', 'patientInfo')->get();
		echo 'Users found: '.$users->count().PHP_EOL;
		foreach($users as $user) {
			echo 'Processing user '.$user->ID.PHP_EOL;
			echo 'Build User'.PHP_EOL;
			$user->first_name = $user->firstName;
			$user->last_name = $user->lastName;
			$user->address = $user->address;
			$user->city = $user->city;
			$user->state = $user->state;
			$user->zip = $user->zip;

			echo 'Build UserDemographics'.PHP_EOL;
			// check if has demographics
			$patientInfo = UserPatientInfo::where('user_id', $user->ID)->first();
			if(!$patientInfo) {
				// create new
				$patientInfo = new UserPatientInfo();
				$patientInfo->user_id = $user->ID;
				$user->patientInfo()->save($patientInfo);
				$user->load('patientInfo');
			}

			// set values
			$user->patientInfo->first_name = $user->firstName;
			$user->patientInfo->last_name = $user->lastName;
			$user->patientInfo->save();
			dd($user->patientInfo);
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

		Schema::drop('user_patient_info');
	}

}
