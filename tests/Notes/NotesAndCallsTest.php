<?php

class NotesAndCallsTest extends TestCase
{

    use \Tests\HandlesUsersAndCarePlans;
    use \Tests\HandlesNotesAndCalls;

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