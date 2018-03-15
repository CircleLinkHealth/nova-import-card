<?php

use Illuminate\Database\Seeder;

class ViewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();
        $this->command->info('views seeder begins ...');
        DB::unprepared(file_get_contents("database/seeds/sql/views/vw_calls.sql"));
        DB::unprepared(file_get_contents("database/seeds/sql/views/vw_user_timezone.sql"));
        $this->command->info('views seeded!');
    }
}
