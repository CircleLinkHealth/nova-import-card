<?php

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/28/16
 * Time: 4:08 PM
 */
class TestDataSeederManager extends \Illuminate\Database\Seeder
{
    public function run()
    {
        $this->call(CpmInstructionSeeder::class);
        $this->command->info(CpmInstructionSeeder::class . ' ran.');
    }
}