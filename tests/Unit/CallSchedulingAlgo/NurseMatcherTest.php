<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit\CallSchedulingAlgo;

use App\Algorithms\Calls\CallAlgoHelper;
use App\Traits\Tests\UserHelpers;
use Carbon\Carbon;
use Tests\TestCase;

class NurseMatcherTest extends TestCase
{
    use CallAlgoHelper;
    use UserHelpers;
    private $matchArray = [];
    private $nurse;
    private $nurse2;

    private $patient;
    private $practice;

    private $prediction = [];

    public function createPatientWindows()
    {
        for ($i = 1; $i < 6; ++$i) {
            $windows[] = $this->createWindowForPatient(
                $this->patient,
                Carbon::parse('10:00:00'),
                Carbon::parse('17:00:00'),
                $i
            );
        }

        return $windows;
    }

    public function test_nurses_match_test()
    {
        //init mock algo predictions
        $this->prediction['date']         = '2016-12-19';
        $this->prediction['window_start'] = '09:00:00';
        $this->prediction['window_end']   = '17:00:00';

        $this->practice = \CircleLinkHealth\Customer\Entities\Practice::create([
            'name' => 'program'.Carbon::now()->secondsSinceMidnight(),
        ]);

        //create main nurse
        $nurse                      = $this->createUser($this->practice->id, 'care-center');
        $this->nurse                = $nurse->nurseInfo;
        $this->prediction['Nurse1'] = $nurse->getFullName();

        //create nurse with matching window
        $nurse2                     = $this->createUser($this->practice->id, 'care-center');
        $this->nurse2               = $nurse2->nurseInfo;
        $this->prediction['Nurse2'] = $nurse2->getFullName();

        $patient                     = $this->createUser($this->practice->id, 'participant');
        $this->patient               = $patient->patientInfo;
        $this->prediction['Patient'] = $patient->getFullName();

        //mock the last success to test for previously contacted nurses
        $call = $this->createLastCallForPatient($this->patient, $this->nurse);

        $this->createPatientWindows();

        $this->createWindowForNurse(
            $this->nurse,
            Carbon::parse('2016-12-17 08:00:00'),
            Carbon::parse('2016-12-17 11:00:00')
        );

        $this->createWindowForNurse(
            $this->nurse2,
            Carbon::parse('2016-12-21 08:00:00'),
            Carbon::parse('2016-12-21 11:00:00')
        );

        $this->findNurse();

        $this->assertTrue($this->prediction['nurse'] == $this->nurse->user_id);
    }
}
