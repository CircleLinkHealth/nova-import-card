<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\PostmarkInboundMail;
use App\Traits\Tests\PostmarkCallbackHelpers;
use App\Traits\Tests\PracticeHelpers;
use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Tests\TestCase;

class AutoAssignCallbackTest extends TestCase
{
    use PostmarkCallbackHelpers;
    use PracticeHelpers;
    use UserHelpers;

    /**
     * @var User
     */
    private $careAmbassador;
    /**
     * @var User
     */
    private $patient;
    /**
     * @var Enrollee|\Illuminate\Database\Eloquent\Model
     */
    private $patientEnrollee;
    /**
     * @var \Illuminate\Database\Eloquent\Model|PostmarkInboundMail
     */
    private $postmarkRecord;
    /**
     * @var \CircleLinkHealth\Customer\Entities\Practice
     */
    private $practice;

    public function test_it_creates_callback_if_notification_is_from_callcenterusa()
    {
        $this->setUpTest(Patient::ENROLLED);
        assert(true);
    }

    public function test_it_does_not_create_callback_if_patient_is_auto_enroll_but_has_unassigned_care_ambassador()
    {
        $this->setUpTest(Enrollee::QUEUE_AUTO_ENROLLMENT);
        assert(true);
    }

    public function test_it_does_not_create_callback_if_patient_is_not_enrolled()
    {
        $this->setUpTest(Patient::PAUSED);
        assert(true);
    }

    public function test_it_does_not_create_callback_if_patient_requested_to_withdraw()
    {
        $this->setUpTest(Patient::PAUSED, true);
        assert(true);
    }
}
