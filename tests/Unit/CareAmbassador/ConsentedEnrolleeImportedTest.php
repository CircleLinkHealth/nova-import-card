<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Traits\Tests\CareAmbassadorHelpers;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Tests\TestCase;

class ConsentedEnrolleeImportedTest extends TestCase
{
    use \App\Traits\Tests\UserHelpers;
    use CareAmbassadorHelpers;
    
    protected $careAmbassadorUser;
    protected $enrollee;
    protected $practice;
    protected $provider;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->practice           = factory(Practice::class)->create();
        $this->careAmbassadorUser = $this->createUser($this->practice->id, 'care-ambassador');
        $this->provider           = $this->createUser($this->practice->id, 'provider');
        $this->enrollee           = factory(Enrollee::class)->create();
        $this->createEligibilityJobDataForEnrollee($this->enrollee);
    }
    
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_consented_enrollee_importing()
    {
        auth()->login($this->careAmbassadorUser);
        
        $otherNote = 'Test this goes to patient info';
        $this->performActionOnEnrollee($this->enrollee, Enrollee::CONSENTED, [
            'extra' => $otherNote,
        ]);
        
        $enrollee = $this->enrollee->fresh();
        
        $this->assertNotNull($enrollee->user);
        $this->assertNotNull($enrollee->user->patientInfo);
        $this->assertNotNull($enrollee->user->patientInfo->general_comment == $otherNote);
    }
    
}
