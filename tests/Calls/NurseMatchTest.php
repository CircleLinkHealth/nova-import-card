<?php

use App\Algorithms\Calls\CallAlgoHelper;
use App\User;
use Tests\Helpers\UserHelpers;

/**
 * Tests CallAlgoHelper::findNurse()
 *
 * Script:
 *
 * Patient1 has just finished a scheduled call and the portion of the
 * algorithm that matches Nurse Windows to determine the next call runs.
 *
 * The Logic looks for the last called Nurse and checks for whether
 * they have a contact window. If not, it then moves on to the
 * other nurses in the system who are allowed to see the
 * program
 *
 */

class NurseMatchTest extends TestCase
{
    private $nurse;
    private $nurse2;
    private $practice;

    private $patient;

    private $prediction = [];
    private $matchArray = [];

    use UserHelpers, CallAlgoHelper;

    public function testNursesMatchTest()
    {

        //init mock algo predictions
        $this->prediction['date'] = '2016-12-19';
        $this->prediction['window_start'] = '09:00:00';
        $this->prediction['window_end'] = '17:00:00';

        $this->practice = \App\Practice::create([

            'name' => 'program' . \Carbon\Carbon::now()->secondsSinceMidnight()

        ]);

        //create main nurse
        $nurse = $this->createUser($this->practice->id, 'care-center');
        $this->nurse = $nurse->nurseInfo;

        //create nurse with matching window
        $nurse2 = $this->createUser($this->practice->id, 'care-center');
        $this->nurse2 = $nurse2->nurseInfo;

        $patient = $this->createUser($this->practice->id, 'participant');
        $this->patient = $patient->patientInfo;

        //mock the last success to test for previously contacted nurses
        $call = $this->createLastCallForPatient($this->patient, $this->nurse);

        $this->createPatientWindows();

        $this->createWindowForNurse($this->nurse,
            Carbon\Carbon::parse('2016-12-17 08:00:00'),
            Carbon\Carbon::parse('2016-12-17 11:00:00'));

        $this->createWindowForNurse($this->nurse2,
            Carbon\Carbon::parse('2016-12-21 08:00:00'),
            Carbon\Carbon::parse('2016-12-21 11:00:00'));

        $this->findNurse();

        dd($this->prediction);

        $this->assertTrue($this->prediction['nurse'] == $this->nurse->user_id);

    }

    public function createPatientWindows(){

        for($i = 1; $i < 6; $i++){
            $windows[] = $this->createWindowForPatient($this->patient,
                Carbon\Carbon::parse('10:00:00'),
                Carbon\Carbon::parse('17:00:00'),
                $i);
        }


        return $windows;
    }

}
