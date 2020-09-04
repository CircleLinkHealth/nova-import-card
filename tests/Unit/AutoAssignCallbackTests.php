<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AutoAssignCallbackTests extends TestCase
{
    //        CASES to trigger. Depending on each will be calculated if callback should be created or left to CA's to decide:

//        1. Queued for Enrollment.
//        2. Non-enrolled Patient Status.
//        3. Patient wants to Cancel/Withdraw.
//        3a. Postmark notification has extra "Cancel/Withdraw Reason" field.
//        3b. If "Cancel/Withdraw Reason" exists, or if the {Msg} section contains any of the following text strings:
//           Cancel,
//           CX,
//           Withdraw
//           - these patients should be left to be manually handled by Ops.
}
