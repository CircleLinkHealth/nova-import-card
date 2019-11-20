<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
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
                $admin->attachPractice($practice->id, [Role::whereName('administrator')->value('id')]);
                $admin->program_id = $practice->id;
                $admin->password = Hash::make('hello');
                $admin->save();

                $this->command->info("admin user $admin->display_name seeded");
            });

            //create nurse
            factory(User::class, 1)->create()->each(function ($nurse) use ($practice) {
                $nurse->username = 'nurse';
                $nurse->email = 'nurse@example.org';
                $nurse->attachPractice($practice->id, [Role::whereName('care-coach')->value('id')]);
                $nurse->program_id = $practice->id;
                $nurse->password = Hash::make('hello');
                $nurse->save();
                $nurse->nurseInfo()->create();

                $this->command->info("nurse user $nurse->display_name seeded");
            });
        } else {
            $this->command->error('user-seeder: no practice found');
        }
    }
}
