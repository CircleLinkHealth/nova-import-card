<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		$this->call('database\seeds\UserTableSeeder');
		$this->command->info('User table seeded!');

		$this->call('database\seeds\ActivityTableSeeder');
		$this->command->info('Activity table seeded!');

		$this->call('database\seeds\LocationTableSeeder');
		$this->command->info('Location table seeded!');
	}

}