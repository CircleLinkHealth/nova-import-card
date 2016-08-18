<?php

use Illuminate\Database\Seeder;
use App\Call;
use App\User;
use Carbon\Carbon;

class CallsTableSeeder extends Seeder {

    public function run()
    {
        $users = User::whereHas('roles', function ($q) {
            $q->where('name', '=', 'participant');
        })->with('patientInfo')->get();
        echo 'Process role patient users - Users found: '.$users->count().PHP_EOL;
        $i = 0;
        foreach($users as $user) {
            echo 'Processing user ' . $user->ID . PHP_EOL;
            $call = Call::
                where('inbound_cpm_id', '=', $user->ID)
                ->where('status', '=', 'scheduled')
                ->first();
            if($call) {
                echo 'Call exists ' . $user->ID . PHP_EOL;
            } else {
                $obj = new Call;
                $obj->call_date = '2016-08-26';
                $obj->window_start = '08:40';
                $obj->window_end = '15:40';
                $obj->service = 'phone';
                $obj->status = 'scheduled';
                $obj->created_at = Carbon::now();
                $obj->updated_at = Carbon::now();
                $obj->inbound_cpm_id = $user->ID;
                $obj->outbound_cpm_id = '2183';
                $obj->is_cpm_outbound = '1';
                $obj->call_time = '0';
                $obj->inbound_phone_number = '111-234-5678';
                $obj->save();
                echo PHP_EOL . ' Call Added - ' . $obj->id;
            }
        }
    }

}