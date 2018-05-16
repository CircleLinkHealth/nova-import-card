<?php

use App\User;
use App\Practice;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{

    public function run()
    {
        $practice = Practice::first();

        if ($practice) {
            // create admin user
            factory(User::class, 1)->create()->each(function ($admin) use ($practice) {
                $admin->username = 'admin';
                $admin->email = 'admin@example.org';
                $admin->attachPractice($practice->id, true, null, 1);
                $admin->program_id = $practice->id;
                $admin->save();

                $this->command->info("admin user $admin->display_name seeded");
            });
            

            //create nurse
            factory(User::class, 1)->create()->each(function ($nurse) use ($practice) {
                $nurse->username = 'nurse';
                $nurse->email = 'nurse@example.org';
                $nurse->attachPractice($practice->id, false, null, 11);
                $nurse->program_id = $practice->id;
                $nurse->save();
                $nurse->nurseInfo()->create();

                $this->command->info("nurse user $nurse->display_name seeded");
            });
        }
        else {
            $this->command->error('user-seeder: no practice found');
        }
    }
}
