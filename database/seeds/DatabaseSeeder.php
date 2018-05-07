<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

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

//      $this->call('CcdImportRoutinesStrategiesTableSeeder');
//      $this->call('CcdImportRoutinesTableSeeder');
//      $this->call('CcdVendorsTableSeeder');
//      $this->call('CpmProblemsTableSeeder');

        $limit = ini_get('memory_limit'); // retrieve the set limit
        ini_set('memory_limit', -1);

        $this->call(AppConfigTableSeeder::class);
        $this->call(CpmProblemsTableSeeder::class);
        $this->call(AddNewDefaultCarePlanTemplate::class);
        $this->call(RolesPermissionsSeeder::class);
        $this->call(SnomedToIcd9MapTableSeeder::class);
        $this->call(AddActiveStatusToPractices::class);
        $this->call(PracticeTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(PatientMonthlySummariesSeeder::class);

        ini_set('memory_limit', $limit);
    }
}
