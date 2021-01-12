<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\ProviderInfo;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Seeder;

class CreateTesterUsersSeeder extends Seeder
{
    public function __construct()
    {
        $this->adminRole    = Role::whereName('administrator')->first();
        $this->providerRole = Role::whereName('provider')->first();
        $this->nurseRole    = Role::whereName('care-center')->first();
    }

    /**
     * Run the database seeds.
     */
    public function run()
    {
        $practice = Practice::whereName('demo')->first();

        //create admin user
        $adminEmail = 'shamim7777@gmail.com';
        $adminUser  = $this->createUser($practice, $adminEmail, 'Rizwana', 'Matin', $this->adminRole);

        //create provider user
        $providerEmail = 'rizwana.matin@gmail.com';
        $providerUser  = $this->createUser($practice, $providerEmail, 'Rizwana', 'Matin', $this->providerRole);

        //create nurse role
        $nurseEmail = 'sytrekinc@gmail.com';
        $nurseUser  = $this->createUser($practice, $nurseEmail, 'Rizwana', 'Matin', $this->nurseRole);

        $this->command->info('Tester User Accounts created.');
    }

    private function createUser(Practice $practice, $email, $firstName, $lastName, Role $role)
    {
        $user = User::updateOrCreate([
            'email' => $email,
        ], [
            'program_id'   => $practice->id,
            'first_name'   => $firstName,
            'last_name'    => $lastName,
            'display_name' => "$firstName $lastName",
        ]);

        $user->attachLocation($practice->locations);
        $user->attachGlobalRole($role);

        $user->attachPractice($practice, [$role->id]);

        if ($role->id == $this->providerRole->id) {
            $providerInfoCreated = ProviderInfo::firstOrCreate([
                'user_id' => $user->id,
            ]);
        }

        if ($role->id == $this->nurseRole->id) {
            $nurseInfoCreated = Nurse::firstOrCreate([
                'user_id' => $user->id,
                'status'  => 'active',
            ]);
        }

        $url = url('auth/password/reset');

        Mail::send('emails.string-content', [
            'content' => "A CLH Test User account was created for you. Please obtain a password from $url. After that you can login using your email and password.",
        ], function ($message) use ($email) {
            $message->from('tester-accounts@careplanmanager.com', 'CircleLink Health');
            $message->to($email)->subject('A CLH Test User account was created for you.');
            $message->cc('mantoniou@circlelinkhealth.com');
        });

        return $user;
    }
}
