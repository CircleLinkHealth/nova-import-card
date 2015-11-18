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
                $meta->meta_value = 'draft';
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
                    ->where('meta_key' , '=', 'careplan_provider_approver')
                    ->delete();
                $removed = WpUserMeta::where('user_id', '=', $user->ID)
                    ->where('meta_key' , '=', 'careplan_provider_date')
                    ->delete();
                $removed = WpUserMeta::where('user_id', '=', $user->ID)
                    ->where('meta_key' , '=', 'careplan_qa_date')
                    ->delete();
                $removed = WpUserMeta::where('user_id', '=', $user->ID)
                    ->where('meta_key' , '=', 'careplan_qa_approver')
                    ->delete();
                $meta = new WpUserMeta;
                $meta->user_id = $user->ID;
                $meta->meta_key = 'careplan_qa_approver';
                //$meta->meta_value = '1';
                $meta->meta_value = '';
                $meta->save();
                //echo 'added careplan_qa_approver = 1 to '.$user->ID.PHP_EOL;
                echo 'added careplan_qa_approver = to '.$user->ID.PHP_EOL;
                $removed = WpUserMeta::where('user_id', '=', $user->ID)
                    ->where('meta_key' , '=', 'careplan_qa_date')
                    ->delete();
                $meta = new WpUserMeta;
                $meta->user_id = $user->ID;
                $meta->meta_key = 'careplan_qa_date';
                //$meta->meta_value = date('Y-m-d H:i:s');
                $meta->meta_value = '';
                $meta->save();
                //echo 'added careplan_qa_date = '.date('Y-m-d H:i:s').' to '.$user->ID.PHP_EOL;
                echo 'added careplan_qa_date = to '.$user->ID.PHP_EOL;
                $meta = new WpUserMeta;
                $meta->user_id = $user->ID;
                $meta->meta_key = 'careplan_provider_approver';
                $meta->meta_value = '';
                $meta->save();
                echo 'added careplan_provider_approver = to '.$user->ID.PHP_EOL;
                $meta = new WpUserMeta;
                $meta->user_id = $user->ID;
                $meta->meta_key = 'careplan_provider_date';
                $meta->meta_value = '';
                $meta->save();
                echo 'added careplan_provider_date = to '.$user->ID.PHP_EOL;
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
                // set based on status
                $meta = new WpUserMeta;
                $meta->user_id = $user->ID;
                $meta->meta_key = 'ccm_status';
                $value = 'paused';
                if($user->program_id) {
                    if ($user->program_id >= 7) {
                        $user_config = WpUserMeta::where('meta_key', '=', 'wp_' . $user->program_id . '_user_config')->first();
                        if (!empty($user_config)) {
                            $user_config = unserialize($user_config->meta_value);
                            if (is_array($user_config)) {
                                if (isset($user_config['status'])) {
                                    if ($user_config['status'] == 'Active') {
                                        $value = 'enrolled';
                                    }
                                }
                            }
                        }
                    }
                }
                $meta->meta_value = $value;
                $meta->save();
                echo 'adding ccm_status = '.$value.' to '.$user->ID.PHP_EOL;
            }
        }

    }
}