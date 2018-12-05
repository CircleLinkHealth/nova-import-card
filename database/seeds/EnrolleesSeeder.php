<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Enrollee;
use Illuminate\Database\Seeder;

class EnrolleesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        factory(Enrollee::class, 10)->create();
    }
}
