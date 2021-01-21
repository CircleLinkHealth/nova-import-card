<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\State;
use Illuminate\Database\Seeder;

class NurseStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $MAX_STATE_ID = 51;
        $stateId      = 1;
        $nurses       = Nurse::get();
        while ($stateId <= $MAX_STATE_ID) {
            for ($i = 0; $i < $nurses->count(); ++$i) {
                $nurse = $nurses[$i];
                if ( ! $nurse->states()->find($stateId)) {
                    $state = State::find($stateId);
                    if ($state) {
                        $nurse->states()->save($state);
                    }
                }
                ++$stateId;
            }
        }
    }
}
