<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Seeder;

class UserScrambler extends Seeder
{
    public function run()
    {
        $allUsers = User::all();

        if (!empty($allUsers)) {
            $u = 0;
            foreach ($allUsers as $user) {
                $role = $user->roles()->first();
                if ($role && 'participant' == strtolower($role->name)) {
                    echo PHP_EOL.PHP_EOL;
                    echo PHP_EOL.$role->name;
                    echo PHP_EOL.$user->id.'-'.$user->email;
                    $user->scramble();
                    echo PHP_EOL.$user->id.'-'.$user->email;
                    ++$u;
                }
            }
        }

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
}
