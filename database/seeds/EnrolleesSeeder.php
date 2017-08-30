<?php

use App\Enrollee;
use Illuminate\Database\Seeder;

class EnrolleesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Enrollee::class, 10)->create();
    }
}
