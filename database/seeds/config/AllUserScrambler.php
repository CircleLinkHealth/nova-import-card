<?php

use App\User;
use App\Permission;
use App\Role;
use App\UserMeta;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;


class AllUserScrambler extends Seeder {

    public function run()
    {
        $allUsers = User::all();

        if(!empty($allUsers)) {
            $json_string = file_get_contents("https://randomuser.me/api/?nat=us&results=".$allUsers->count());
            if (empty($json_string)) {
                return false;
            }
            $randomUsers = json_decode($json_string);
            $u = 0;
            foreach($allUsers as $user) {
                if(isset($randomUsers->results[$u])) {
                    $randomUserInfo = $randomUsers->results[$u];
                    $role = $user->roles()->first();
                    if ($role && strtolower($role->name) == 'participant') {
                        echo PHP_EOL.PHP_EOL;
                        echo PHP_EOL . $role->name;
                        echo PHP_EOL . $user->ID . '-' . $user->user_email;
                        $user->scramble($randomUserInfo);
                        echo PHP_EOL . $user->ID . '-' . $user->user_email;
                        $u++;
                    }
                }
            }
        }

        echo PHP_EOL .'Truncating ccdas table..';
        DB::table('ccdas')->delete();
        echo PHP_EOL .'Truncated';
    }

}