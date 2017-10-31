<?php

use Tests\TestCase;
use App\Algorithms\Calls\CallAlgoHelper;
use App\User;
use Tests\Helpers\UserHelpers;

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
        $this->prediction['Nurse1'] = $nurse->fullName;

        //create nurse with matching window
        $nurse2 = $this->createUser($this->practice->id, 'care-center');
        $this->nurse2 = $nurse2->nurseInfo;
        $this->prediction['Nurse2'] = $nurse2->fullName;


        $patient = $this->createUser($this->practice->id, 'participant');
        $this->patient = $patient->patientInfo;
        $this->prediction['Patient'] = $patient->fullName;


        //mock the last success to test for previously contacted nurses
        $call = $this->createLastCallForPatient($this->patient, $this->nurse);

        $this->createPatientWindows();

        $this->createWindowForNurse(
            $this->nurse,
            Carbon\Carbon::parse('2016-12-17 08:00:00'),
            Carbon\Carbon::parse('2016-12-17 11:00:00')
        );

        $this->createWindowForNurse(
            $this->nurse2,
            Carbon\Carbon::parse('2016-12-21 08:00:00'),
            Carbon\Carbon::parse('2016-12-21 11:00:00')
        );

        $this->findNurse();

        dd($this->prediction);

        $this->assertTrue($this->prediction['nurse'] == $this->nurse->user_id);
    }

    public function createPatientWindows()
    {

        for ($i = 1; $i < 6; $i++) {
            $windows[] = $this->createWindowForPatient(
                $this->patient,
                Carbon\Carbon::parse('10:00:00'),
                Carbon\Carbon::parse('17:00:00'),
                $i
            );
        }


        return $windows;
    }
}
