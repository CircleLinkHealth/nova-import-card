<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Core\Tests\CreatesApplication;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Jobs\ProcessPostmarkInboundMailJob;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\PostmarkInboundMail;
use CircleLinkHealth\SharedModels\Entities\PostmarkInboundMailRequest;
use CircleLinkHealth\SharedModels\Traits\Tests\PostmarkCallbackHelpers;
use Faker\Factory;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * This test uses MySQL Search, so it must commit transactions to DB.
 * Notice that it extends the BaseTestCase and DOES NOT use the trait
 * {@link \Illuminate\Foundation\Testing\DatabaseTransactions}.
 */
class AutoAssignCallbackTest2 extends BaseTestCase
{
    use CreatesApplication;
    use PostmarkCallbackHelpers;
    use PracticeHelpers;
    use UserHelpers;

    private User $careAmbassador;
    private User $patient;
    private Enrollee $patientEnrollee;
    private $phone;
    private PostmarkInboundMail $postmarkRecord;
    private Practice $practice;
    private User $standByNurse;

    public function setUp(): void
    {
        parent::setUp();
        $this->practice       = $this->practiceForSeeding();
        $this->careAmbassador = $this->createUser($this->practice->id, 'care-ambassador');
    }

    public function test_enrollee_requests_callback_without_user_model()
    {
        $faker                     = Factory::create();
        $fakePatient               = new User();
        $fakePatient->id           = null;
        $fakePatient->first_name   = $faker->firstName;
        $fakePatient->last_name    = $faker->lastName;
        $fakePatient->display_name = $fakePatient->first_name.' '.$fakePatient->last_name;
        $phone                     = new PhoneNumber();
        $phone->number             = (new StringManipulation())->formatPhoneNumberE164($faker->phoneNumber);
        $fakePatient->setRelation('phoneNumbers', collect([$phone]));
        $this->createEnrolleeWithStatus($fakePatient, $this->careAmbassador->id, Enrollee::CONSENTED, $this->practice->id);
        $postmarkRecord = $this->createPostmarkCallbackData(false, true, $fakePatient);
        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord->data))->toArray(), $postmarkRecord->id);
        $this->assertDatabaseHas('unresolved_postmark_callbacks', [
            'postmark_id' => $postmarkRecord->id,
        ]);
    }

    private function dispatchPostmarkInboundMail(array $recordData, int $recordId)
    {
        ProcessPostmarkInboundMailJob::dispatchNow(
            new PostmarkInboundMailRequest($recordData),
            $recordId
        );
    }
}
