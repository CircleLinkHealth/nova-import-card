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
            foreach($allUsers as $user) {
                $role = $user->roles()->first();
                if($role && strtolower($role->name) != 'administrator' && strtolower($role->name) != 'provider') {
                    echo PHP_EOL;
                    echo PHP_EOL;
                    echo PHP_EOL.$role->name;
                    echo PHP_EOL.$user->ID. '-' . $user->user_email;
                    $user->scramble();
                    echo PHP_EOL.$user->ID. '-' . $user->user_email;
                }
            }
        }
    }

}