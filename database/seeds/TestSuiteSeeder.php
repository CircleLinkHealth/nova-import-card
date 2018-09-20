<?php

use Illuminate\Database\Seeder;

class TestSuiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(PracticeTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(PatientSeeder::class);
    }
}
