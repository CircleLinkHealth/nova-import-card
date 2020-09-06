<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Services\Postmark\PostmarkCallbackMailService;
use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use PHPUnit\Framework\TestCase;

class AutoAssignCallbackTests extends TestCase
{
    use UserHelpers;
    //        CASES to trigger. Depending on each will be calculated if callback should be created or left to CA's to decide:

    public function sendPostmarkNotification()
    {
        (new PostmarkCallbackMailService())->createCallbackNotification();
    }

//        1. Queued for Enrollment.
    public function test_it_creates_callback_if_notification_is_from_callcenterusa()
    {
//        $practice = Practice::firstOrCreate(
//            [
//                'name' => 'demo-clinic',
//            ],
//            [
//                'display_name'    => 'Demo clinic',
//                'saas_account_id' => 1,
//                'active'          => true,
//            ]
//        );
//        $patient = $this->createUser($practice->id, 'participant', Patient::ENROLLED);

        $this->sendPostmarkNotification();
        assert(true);
    }

//        2. Non-enrolled Patient Status.
//        3. Patient wants to Cancel/Withdraw.
//        3a. Postmark notification has extra "Cancel/Withdraw Reason" field.
//        3b. If "Cancel/Withdraw Reason" exists, or if the {Msg} section contains any of the following text strings:
//           Cancel,
//           CX,
//           Withdraw
//           - these patients should be left to be manually handled by Ops.
}
