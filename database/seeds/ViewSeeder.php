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
        $tables = [
            "database/seeds/sql/views/vw_calls.sql",
            "database/seeds/sql/views/vw_user_timezone.sql"
        ];
        Eloquent::unguard();
        $this->command->info('views seeder begins ...');
        foreach ($tables as $table) {
            DB::unprepared(file_get_contents($table));
            echo $table."\n";
        }
        $this->command->info('views seeded!');
    }
}
