<?php

use App\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class S20151112PatientRoleUpdate extends Seeder
{

    public function run()
    {
        // careplan_status

        // get users without careplan_status
        echo PHP_EOL.PHP_EOL.'Users missing careplan_status'.PHP_EOL.PHP_EOL;
        $users = WpUser::whereHas('meta', function ($q) {
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
    }
}