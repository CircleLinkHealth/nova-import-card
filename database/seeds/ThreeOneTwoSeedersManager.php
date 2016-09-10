<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class ThreeOneTwoSeedersManager extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call('StatesTableSeeder');
        $this->call('AddExistingNurses');
        $this->call('PatientSummaryTableSeeder');
        $this->call('S20160910ReconcileCallAttempts');
    }
}
