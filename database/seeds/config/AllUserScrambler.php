<?php

use App\User;
use App\Permission;
use App\Role;
use App\UserMeta;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;


class AllUserScrambler extends Seeder
{

    public function run()
    {
        $allUsers = User::all();

        if (!empty($allUsers)) {
            $u = 0;
            foreach ($allUsers as $user) {
                $role = $user->roles()->first();
                if ($role && strtolower($role->name) == 'participant') {
                    echo PHP_EOL . PHP_EOL;
                    echo PHP_EOL . $role->name;
                    echo PHP_EOL . $user->ID . '-' . $user->user_email;
                    $user->scramble();
                    echo PHP_EOL . $user->ID . '-' . $user->user_email;
                    $u++;
                }
            }
        }

        echo PHP_EOL . 'Truncating ccdas table..';
        DB::table('ccdas')->delete();
        echo PHP_EOL . 'Truncated';

        DB::table('ccd_allergy_logs')->delete();
        DB::table('ccd_demographics_logs')->delete();
        DB::table('ccd_document_logs')->delete();
        DB::table('ccd_medication_logs')->delete();
        DB::table('ccd_problem_logs')->delete();
        DB::table('ccd_provider_logs')->delete();
    }

}