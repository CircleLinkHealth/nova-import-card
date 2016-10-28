<?php

use App\CarePlanCareSection;
use App\User;
use Illuminate\Database\Seeder;

class S20160602UserAddressMigration extends Seeder {


    public function run()
    {
        $this->migrateUserAddress();
    }

    public function migrateUserAddress()
    {
        // seed data user demographics
        $users = User::withTrashed()->with('meta')->where('ID', '<', '2065')->get();
        echo 'Process all role users demographics - Users found: '.$users->count().PHP_EOL;
        foreach($users as $user) {
            echo 'Processing user '.$user->ID.PHP_EOL;
            echo 'Rebuild User address / address2 / zip / status'.PHP_EOL;
            $user->first_name = $user->getUserMetaByKey('first_name');
            $user->last_name = $user->getUserMetaByKey('last_name');
            $user->city = $user->getUserConfigByKey('city');
            $user->state = $user->getUserConfigByKey('state');
            $user->address = $user->getUserConfigByKey('address');
            $user->address2 = $user->getUserConfigByKey('address2');
            $user->zip = $user->getUserConfigByKey('zip');
            $user->status = '';
            $user->save();

            echo 'Saved '.PHP_EOL;
        }
    }
}