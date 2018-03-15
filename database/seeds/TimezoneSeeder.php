<?php

use Illuminate\Database\Seeder;

class TimezoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();
        $this->command->info('timezone tables seeder begins ...');
        for ($i = 0; $i <= 2; $i++) {
            $path = "database/seeds/sql/timezones-$i.sql";
            DB::unprepared(file_get_contents($path));
        }
        $this->command->info('timezone tables seeded!');
    }
}
