<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Database\Seeder;

class CareplanJsonStatusFixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $careplans = CarePlan::where('status', 'LIKE', '{%')->get();
        $careplans->map(function ($careplan) {
            if ($careplan->status && is_json($careplan->status)) {
                $status = ((array) json_decode($careplan->status))['status'];
                $this->command->info('set '.$careplan->user_id.' to '.$status);
                $careplan->status = $status;
                $careplan->save();
            }
        });
    }
}
