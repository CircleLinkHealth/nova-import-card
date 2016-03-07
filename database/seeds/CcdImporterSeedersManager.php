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