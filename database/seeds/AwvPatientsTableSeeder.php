<?php

use App\AwvPatients;
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
        factory(AwvPatients::class, 100)->create();
    }
}
