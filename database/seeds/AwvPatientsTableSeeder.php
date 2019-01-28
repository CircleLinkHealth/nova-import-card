<?php

use App\awvPatients;
use Illuminate\Database\Seeder;

class AwvPatientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(awvPatients::class, 100)->create();
    }
}
