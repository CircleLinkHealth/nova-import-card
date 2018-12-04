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
        $this->call(RequiredRolesPermissionsSeeder::class);
        $this->call(CpmDefaultInstructionSeeder::class);

        $this->command->info('All Seeders ran!');
    }
}
