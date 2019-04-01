<?php

use Illuminate\Database\Seeder;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\State;

class NurseStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $MAX_STATE_ID = 51;
        $stateId = 1;
        $nurses = Nurse::get();
        while ($stateId <= $MAX_STATE_ID) {
            for ($i = 0; $i < $nurses->count(); $i++) {
                $nurse = $nurses[$i];
                if (!$nurse->states()->find($stateId)) {
                    $state = State::find($stateId);
                    if ($state) {
                        $nurse->states()->save($state);
                    }
                }
                $stateId += 1;
            }
        }
    }
}
