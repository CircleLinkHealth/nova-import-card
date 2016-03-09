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
                $user->scramble();
            }
        }
    }

}