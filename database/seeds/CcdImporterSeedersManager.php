<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class CcdImporterSeedersManager extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		/*
		$this->call('database\seeds\UserTableSeeder');
		$this->command->info('User table seeded!');

		$this->call('database\seeds\ActivityTableSeeder');
		$this->command->info('Activity table seeded!');

		$this->call('database\seeds\LocationTableSeeder');
		$this->command->info('Location table seeded!');

		$this->call('database\seeds\UserProgramSeeder');
		$this->command->info('User table program ids seeded!');
		*/

		// ObservationsCommentsSeeder, kg 2015/9/24
		// S20150929SymItems, kg 2015/9/28
//	    $this->call('SnomedToIcd10MapTableSeeder');
//		$this->command->info('SnomedToIcd10MapTableSeeder success!');


		//disable foreign key check for this connection before running seeders
		DB::statement('SET FOREIGN_KEY_CHECKS=0;');

		$this->call('CcdVendorsTableSeeder');
		$this->command->info('CcdVendorsTableSeeder success!');

		$this->call('CcdImportRoutinesTableSeeder');
		$this->command->info('CcdImportRoutinesTableSeeder success!');

		$this->call('CcdImportRoutinesStrategiesTableSeeder');
		$this->command->info('CcdImportRoutinesStrategiesTableSeeder success!');

		$this->call('CpmProblemsTableSeeder');
		$this->command->info('CpmProblemsTableSeeder success!');

		//enable foreign key checks
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}

}