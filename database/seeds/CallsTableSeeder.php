<?php

use Illuminate\Database\Seeder;
use App\Call;
use Carbon\Carbon;

class CallsTableSeeder extends Seeder {

    public function run()
    {
        for ($i = 0; $i < 1000; $i++) {
            $obj = new Call;
            $obj->call_date = '2016-08-20';
            $obj->window_start = '08:40';
            $obj->window_end = '15:40';
            $obj->service = 'phone';
            $obj->status = 'scheduled';
            $obj->created_at = Carbon::now();
            $obj->updated_at = Carbon::now();
            $obj->inbound_cpm_id = '1020';
            $obj->outbound_cpm_id = '1020';
            $obj->is_cpm_outbound = '1';
            $obj->call_time = '0';
            $obj->inbound_phone_number = '111-234-5678';
            $obj->save();
            echo PHP_EOL . ' Call Added - ' . $obj->id;
        }
    }

}