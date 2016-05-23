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

		$this->call(SnomedToIcd10MapTableSeeder::class);
		$this->command->info('SnomedToIcd10MapTableSeeder success!');

		$this->call(CcdVendorsTableSeeder::class);
		$this->command->info('CcdVendorsTableSeeder success!');

		$this->call(CcdImportRoutinesTableSeeder::class);
		$this->command->info('CcdImportRoutinesTableSeeder success!');

		$this->call(CcdImportRoutinesStrategiesTableSeeder::class);
		$this->command->info('CcdImportRoutinesStrategiesTableSeeder success!');

		$this->call(CpmProblemsTableSeeder::class);
		$this->command->info('CpmProblemsTableSeeder success!');

		Artisan::call('map:snomedtocpm');
		$this->command->info('SnomedToCpmIcdMap seeded!');

		//enable foreign key checks
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}

}