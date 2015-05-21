<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Activity;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		$this->call('UserTableSeeder');
		$this->command->info('User table seeded!');

		$this->call('ActivityTableSeeder');
		$this->command->info('User table seeded!');
	}

}


class UserTableSeeder extends Seeder {

	public function run()
	{
		DB::table('users')->delete();

		User::create([
			'name' => 'Michalis Antoniou',
			'email' => 'mantoniou@circlelikhealth.com',
			'password' => Hash::make('iamadmin')
		]);
	}

}


class ActivityTableSeeder extends Seeder {

	public function run()
	{
		DB::table('activities')->delete();

		Activity::create([
			'type' => 'Care Plan Setup',
			'duration' => 12,
			'duration_unit' => 'minutes',
			'logged_from' => 'ui'
		]);
	}

}