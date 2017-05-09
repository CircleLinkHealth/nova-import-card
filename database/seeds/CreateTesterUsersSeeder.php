<?php

use App\Nurse;
use App\Practice;
use App\ProviderInfo;
use App\Role;
use App\User;
use Illuminate\Database\Seeder;

class CreateTesterUsersSeeder extends Seeder
{
    public function __construct()
    {
        $this->adminRole = Role::whereName('administrator')->first();
        $this->providerRole = Role::whereName('provider')->first();
        $this->nurseRole = Role::whereName('care-center')->first();
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $practice = Practice::whereName('demo')->first();

        //create admin user
        $adminEmail = 'shamim7777@gmail.com';
        $adminUser = $this->createUser($practice, $adminEmail, 'Rizwana', 'Matin', $this->adminRole);

        //create provider user
        $providerEmail = 'rizwana.matin@gmail.com';
        $providerUser = $this->createUser($practice, $providerEmail, 'Rizwana', 'Matin', $this->providerRole);

        //create nurse role
        $nurseEmail = 'sytrekinc@gmail.com';
        $nurseUser = $this->createUser($practice, $nurseEmail, 'Rizwana', 'Matin', $this->nurseRole);

        $this->command->info('Tester User Accounts created.');
    }

    private function createUser(Practice $practice, $email, $firstName, $lastName, Role $role)
    {
        $password = 'password';

        $user = User::updateOrCreate([
            'email' => $email,
        ], [
            'program_id'   => $practice->id,
            'first_name'   => $firstName,
            'last_name'    => $lastName,
            'display_name' => "$firstName $lastName",
            'password'     => bcrypt($password),
        ]);

        $user->attachLocation($practice->locations);
        $user->attachGlobalRole($role);

        $attachPractice = $user->attachPractice($practice, true, false, $role->id);

        if ($role->id == $this->providerRole->id) {
            $providerInfoCreated = ProviderInfo::firstOrCreate([
                'user_id' => $user->id,
            ]);
        }

        if ($role->id == $this->nurseRole->id) {
            $nurseInfoCreated = Nurse::firstOrCreate([
                'user_id' => $user->id,
            ]);
        }

        return $user;
    }
}
