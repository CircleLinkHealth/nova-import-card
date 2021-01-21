<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Seeder;

class DayOfWeekSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Eloquent::unguard();
        $this->command->info('days_of_week tables seeder begins ...');
        $path = 'database/seeds/sql/days_of_week.sql';
        DB::unprepared(file_get_contents($path));
        $this->command->info('days_of_week tables seeded!');
    }
}
