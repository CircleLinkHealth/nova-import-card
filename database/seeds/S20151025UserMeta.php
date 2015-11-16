<?php

use App\WpUser;
use App\WpUserMeta;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class S20151025UserMeta extends Seeder {

    public function run()
    {
        // careplan_status

        // get users without careplan_status
        echo PHP_EOL.PHP_EOL.'Users missing careplan_status'.PHP_EOL.PHP_EOL;
        $users = WpUser::whereDoesntHave('meta', function ($q) {
            $q->where('meta_key', '=', 'careplan_status');
        })
            ->orWhereHas('meta', function ($q) {
                $q->where('meta_key', '=', 'careplan_status');
                $q->where('meta_value', '=', '');
            })
            ->get();

        if(count($users) > 0) {
            foreach($users as $user) {
                $removed = WpUserMeta::where('user_id', '=', $user->ID)
                    ->where('meta_key' , '=', 'careplan_status')
                    ->delete();
                $meta = new WpUserMeta;
                $meta->user_id = $user->ID;
                $meta->meta_key = 'careplan_status';
                $meta->meta_value = 'qa_approved';
                $meta->save();
                echo 'added careplan_status = qa_approved to '.$user->ID.PHP_EOL;
            }
        }

        // careplan_qa_approver / careplan_qa_date

        // get users without careplan_qa_approver
        echo PHP_EOL.PHP_EOL.'Users missing careplan_qa_approver'.PHP_EOL.PHP_EOL;
        $users = WpUser::whereDoesntHave('meta', function ($q) {
            $q->where('meta_key', '=', 'careplan_qa_approver');
        })
            ->orWhereHas('meta', function ($q) {
                $q->where('meta_key', '=', 'careplan_qa_approver');
                $q->where('meta_value', '=', '');
            })
            ->get();

        if(count($users) > 0) {
            foreach($users as $user) {
                $removed = WpUserMeta::where('user_id', '=', $user->ID)
                    ->where('meta_key' , '=', 'careplan_approved')
                    ->delete();
                $removed = WpUserMeta::where('user_id', '=', $user->ID)
                    ->where('meta_key' , '=', 'careplan_approver')
                    ->delete();
                $removed = WpUserMeta::where('user_id', '=', $user->ID)
                    ->where('meta_key' , '=', 'careplan_qa_approver')
                    ->delete();
                $meta = new WpUserMeta;
                $meta->user_id = $user->ID;
                $meta->meta_key = 'careplan_qa_approver';
                $meta->meta_value = '1';
                $meta->save();
                echo 'added careplan_qa_approver = qa_approved to '.$user->ID.PHP_EOL;
                $removed = WpUserMeta::where('user_id', '=', $user->ID)
                    ->where('meta_key' , '=', 'careplan_qa_date')
                    ->delete();
                $meta = new WpUserMeta;
                $meta->user_id = $user->ID;
                $meta->meta_key = 'careplan_qa_date';
                $meta->meta_value = date('Y-m-d H:i:s');
                $meta->save();
                echo 'added careplan_qa_date = '.date('Y-m-d H:i:s').' to '.$user->ID.PHP_EOL;
            }
        }

        // ccm_status

        // get users without ccm_status
        echo PHP_EOL.PHP_EOL.'Users missing ccm_status'.PHP_EOL.PHP_EOL;
        $users = WpUser::whereDoesntHave('meta', function ($q) {
            $q->where('meta_key', '=', 'ccm_status');
        })
            ->orWhereHas('meta', function ($q) {
                $q->where('meta_key', '=', 'ccm_status');
                $q->where('meta_value', '=', '');
            })
            ->get();

        if(count($users) > 0) {
            foreach($users as $user) {
                $removed = WpUserMeta::where('user_id', '=', $user->ID)
                    ->where('meta_key' , '=', 'ccm_enabled')
                    ->delete();
                $removed = WpUserMeta::where('user_id', '=', $user->ID)
                    ->where('meta_key' , '=', 'ccm_status')
                    ->delete();
                $meta = new WpUserMeta;
                $meta->user_id = $user->ID;
                $meta->meta_key = 'ccm_status';
                $meta->meta_value = 'paused'; // set everyone to true
                $meta->save();
                echo 'adding ccm_status = paused to '.$user->ID.PHP_EOL;
            }
        }

    }
}