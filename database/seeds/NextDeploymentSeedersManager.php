<?php

use Illuminate\Database\Seeder;

class NextDeploymentSeedersManager extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RolesPermissionsSeeder::class);
        $this->call(UpdateBHIProblems::class);
        $this->call(CpmDefaultInstructionSeeder::class);

        $this->command->info('All Seeders ran!');
    }
}
