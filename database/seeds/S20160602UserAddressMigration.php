<?php

use App\Program;
use App\CarePlan;
use App\CareSection;
use App\CareItem;
use App\CarePlanCareSection;
use App\CarePlanItem;
use App\CPRulesUCP;
use App\CPRulesQuestions;
use App\CPRulesItem;
use App\CPRulesItemMeta;
use App\PatientInfo;
use App\ProviderInfo;
use App\PatientCareTeamMember;
use App\PhoneNumber;
use App\User;
use App\UserMeta;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class S20160602UserAddressMigration extends Seeder {


    public function run()
    {
        $this->migrateUserAddress();
    }

    public function migrateUserAddress()
    {
        // seed data user demographics
        $users = User::with('meta')->where('ID', '<', '2065')->get();
        echo 'Process all role users demographics - Users found: '.$users->count().PHP_EOL;
        foreach($users as $user) {
            echo 'Processing user '.$user->ID.PHP_EOL;
            echo 'Rebuild User address / address2 / zip / status'.PHP_EOL;
            $user->address = $user->getUserConfigByKey('address');
            $user->address2 = $user->getUserConfigByKey('address2');
            $user->zip = $user->getUserConfigByKey('zip');
            $user->status = $user->getUserConfigByKey('status');
            $user->save();

            echo 'Saved '.PHP_EOL;
        }
    }
}