<?php

use App\WpUser;
use App\CPRulesItemMeta;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class S20151025UserMeta extends Seeder {

    public function run()
    {
        // careplan_approved

        // get users without careplan_approved
        echo PHP_EOL.PHP_EOL.'Users missing careplan_approved'.PHP_EOL.PHP_EOL;
        $users = WpUser::whereDoesntHave('meta', function ($q) {
                $q->where('meta_key', '=', 'careplan_approved');
                $q->where('meta_value', '=', '');
            })
            ->get();

        if(count($users) > 0) {
            foreach($users as $user) {
                $meta = new CPRulesItemMeta;
                $meta->user_id = $user->ID;
                $meta->meta_key = 'careplan_approved';
                $meta->meta_value = '';
                if($user->program_id == 10) { // miller(10) = true
                    $meta->meta_value = 'true';
                }
                //$meta->save();
                echo 'added careplan_approved to '.$user->ID.PHP_EOL;
            }
        }

        // careplan_approver

        // get users without careplan_approver
        echo PHP_EOL.PHP_EOL.'Users missing careplan_approver'.PHP_EOL.PHP_EOL;
        $users = WpUser::whereDoesntHave('meta', function ($q) {
            $q->where('meta_key', '=', 'careplan_approver');
            $q->where('meta_value', '=', '');
        })
            ->get();

        if(count($users) > 0) {
            foreach($users as $user) {
                $meta = new CPRulesItemMeta;
                $meta->user_id = $user->ID;
                $meta->meta_key = 'careplan_approver';
                $meta->meta_value = '';
                if($user->program_id == 10) { // miller(10) = system
                    $meta->meta_value = 'system';
                }
                //$meta->save();
                echo 'added careplan_approver to '.$user->ID.PHP_EOL;
            }
        }

        // ccm_enabled

        // get users without ccm_enabled
        echo PHP_EOL.PHP_EOL.'Users missing ccm_enabled'.PHP_EOL.PHP_EOL;
        $users = WpUser::whereDoesntHave('meta', function ($q) {
            $q->where('meta_key', '=', 'ccm_enabled');
            $q->where('meta_value', '=', '');
        })
            ->get();

        if(count($users) > 0) {
            foreach($users as $user) {
                $meta = new CPRulesItemMeta;
                $meta->user_id = $user->ID;
                $meta->meta_key = 'ccm_enabled';
                $meta->meta_value = 'true'; // set everyone to true
                //$meta->save();
                echo 'adding ccm_enabled to '.$user->ID.PHP_EOL;
            }
        }
        dd('END');

    }
}