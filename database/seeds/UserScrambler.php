<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;

class UserScrambler extends Seeder
{
    use WithFaker;

    public function run()
    {
        User::whereDoesntHave('roles', function ($q) {
            $q->where('name', '=', 'administrator');
        })->chunk(300, function ($users) {
            foreach ($users as $user) {
                echo PHP_EOL.$user->id.'-'.$user->email;
                $user->scramble();
                echo PHP_EOL.$user->id.'-'.$user->email;
            }
        });

        echo PHP_EOL.'Truncating ccdas table..';
        DB::table('ccdas')->delete();
        echo PHP_EOL.'Truncated';

        DB::table('ccd_allergy_logs')->delete();
        DB::table('ccd_demographics_logs')->delete();
        DB::table('ccd_document_logs')->delete();
        DB::table('ccd_medication_logs')->delete();
        DB::table('ccd_problem_logs')->delete();
        DB::table('ccd_provider_logs')->delete();

        Artisan::call('db:seed', [
            '--class' => CreateTesterUsersSeeder::class,
        ]);
    }

    public function scramble(User $user)
    {
        $faker = $this->faker;

        $user->setFirstName($faker->firstName);
        $user->setLastName('Z-'.$faker->lastName);
        $user->username = $faker->userName;
        $user->password = $faker->password;
        $user->email    = $faker->freeEmail;
        $user->setMRN(rand());
        $user->setGender($faker->randomElement(['F', 'M']));
        $user->address  = $faker->address;
        $user->address2 = $faker->secondaryAddress;
        $user->city     = $faker->city;
        $user->state    = $faker->stateAbbr;
        $user->zip      = $faker->postcode;
        $user->setPhone(formatPhoneNumberE164($faker->phoneNumber));
        $user->setWorkPhoneNumber(formatPhoneNumberE164($faker->phoneNumber));
        $user->setMobilePhoneNumber(formatPhoneNumberE164($faker->phoneNumber));
        $user->setBirthDate($faker->dateTimeThisCentury->format('Y-m-d'));
        $user->setAgentName($faker->name);
        $user->setAgentPhone(formatPhoneNumberE164($faker->phoneNumber));
        $user->setAgentEmail($faker->safeEmail);
        $user->setAgentRelationship('SA');
        $user->save();
    }
}
