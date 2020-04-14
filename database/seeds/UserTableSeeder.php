<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Seeder;
use Illuminate\Validation\ValidationException;

class UserTableSeeder extends Seeder
{
    use UserHelpers;

    public function run()
    {
        $practice = Practice::first();

        if (! $practice) {
            $this->command->error('user-seeder: no practice found');
            
            return;
        }
        
        try {
            // create admin user
            factory(User::class, 1)->create(['saas_account_id' => $practice->saas_account_id])->each(function ($admin) use ($practice) {
                $admin->username = 'admin';
                $admin->auto_attach_programs = true;
                $admin->email = 'admin@example.org';
                $admin->attachPractice($practice->id, [Role::whereName('administrator')->value('id')]);
                $admin->program_id = $practice->id;
                $admin->password = Hash::make('hello');
                $admin->save();
        
                $this->command->info("admin user $admin->display_name seeded");
            });
    
            //create nurse
            factory(User::class, 1)->create(['saas_account_id' => $practice->saas_account_id])->each(function ($nurse) use ($practice) {
                $nurse->username = 'nurse';
                $nurse->auto_attach_programs = true;
                $nurse->email = 'nurse@example.org';
                $nurse->attachPractice($practice->id, [Role::whereName('care-center')->value('id')]);
                $nurse->program_id = $practice->id;
                $nurse->password = Hash::make('hello');
                $nurse->save();
                $info = $nurse->nurseInfo()->create();
                $info->status = 'active';
                $info->save();
        
                $this->command->info("nurse user $nurse->display_name seeded");
            });
    
            $provider                       = $this->createUser($practice, 'provider');
            $provider->username             = 'provider';
            $provider->auto_attach_programs = true;
            $provider->password             = Hash::make('hello');
            $provider->saas_account_id      = $practice->saas_account_id;
            $provider->save();
    
            $careCenter                       = $this->createUser($practice, 'care-center-external');
            $careCenter->username             = 'care-center-external';
            $careCenter->auto_attach_programs = true;
            $careCenter->password             = Hash::make('hello');
            $careCenter->saas_account_id      = $practice->saas_account_id;
            $careCenter->save();
    
            $careAmbassador                  = $this->createUser($practice, 'care-ambassador');
            $careAmbassador->username        = 'care-ambassador';
            $careAmbassador->auto_attach_programs = true;
            $careAmbassador->password        = Hash::make('hello');
            $careAmbassador->saas_account_id = $practice->saas_account_id;
            $careAmbassador->save();
    
            $p                  = $this->createUser($practice, 'participant');
            $p->saas_account_id = $practice->saas_account_id;
            $p->save();
    
            $p                  = $this->createUser($practice, 'participant');
            $p->saas_account_id = $practice->saas_account_id;
            $p->save();
    
            $p                  = $this->createUser($practice, 'participant');
            $p->saas_account_id = $practice->saas_account_id;
            $p->save();
        } catch (ValidationException $e) {
            dd($e->validator->errors()->all());
        }
    }
}
