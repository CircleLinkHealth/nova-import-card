<?php

use Tests\Helpers\CarePlanHelpers;
use Tests\Helpers\NoteAndCallHelpers;
use Tests\Helpers\UserHelpers;

class NotesAndCallsTest extends TestCase
{

    use CarePlanHelpers,
        NoteAndCallHelpers,
        UserHelpers;

    private $provider;
    private $patient;


    public function testAddNote()
    {

        $testStatus = '';

        //Create Provider
        $this->provider = $this->createUser();
        $testStatus .= 'Test user ' . $this->provider->fullName . 'was created. ';
        $this->userLogin($this->provider);

        //Create Patient
        $this->patient = $this->createNewPatient();
        $this->fillCarePlan($this->patient, 14);
        $testStatus .= 'Test provider ' . $this->provider->fullName . 'was created. ';

        echo $testStatus;

        $this->createNote($this->patient, $this->provider);

        
    }


}