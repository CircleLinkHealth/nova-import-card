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
    private $program;

    private $patient;

    private $prediction = [];
    private $matchArray = [];

    use UserHelpers, CallAlgoHelper;

    public function testNursesMatchTest()
    {

        $this->prediction['date'] = '2016-12-16';
        $this->prediction['window_start'] = '09:00:00';
        $this->prediction['window_end'] = '17:00:00';

        $nurse = $this->createUser(9, 'care-center');
        $this->nurse = $nurse->nurseInfo;

        $patient = $this->createUser(9, 'participant');
        $this->patient = $patient->patientInfo;

        $call = $this->createLastCallForPatient($this->patient, $this->nurse);

        $patientWindow = $this->createWindowForPatient($this->patient,
            Carbon\Carbon::parse('10:00:00'),
            Carbon\Carbon::parse('17:00:00'),
            5);


        $windowNurse1 = $this->createWindowForNurse($this->nurse,
                                     Carbon\Carbon::parse('2016-12-16 08:00:00'),
                                     Carbon\Carbon::parse('2016-12-16 11:00:00'));


        $this->findNurse();

        $this->assertTrue($this->prediction['nurse'] == $this->nurse->user_id);


    }


    public function makeWindowsForNurse(User $nurse){



        $this->nurse1->windows()->save([

        ]);

    }




}
