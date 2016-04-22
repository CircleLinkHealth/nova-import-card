<?php
use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/21/16
 * Time: 12:20 PM
 */
class CpmSeedersManager extends \Illuminate\Database\Seeder
{
    public function run()
    {
        Model::unguard();

        $this->call(CpmLifestyleSeeder::class);
        $this->command->info(CpmLifestyleSeeder::class . ' ran.');

        $this->call(CpmMedicationsSeeder::class);
        $this->command->info(CpmMedicationsSeeder::class . ' ran.');

        $this->call(CpmProblemsSeeder::class);
        $this->command->info(CpmProblemsSeeder::class . ' ran.');

        $this->call(CpmSymptomsSeeder::class);
        $this->command->info(CpmSymptomsSeeder::class . ' ran.');

    }

}