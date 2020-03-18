<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\Practice;
use Tests\TestCase;

class CareCoachImporterTest extends TestCase
{
    use \App\Traits\Tests\UserHelpers;

    /**
     * A basic test example.
     */
    public function test_care_coach_can_see_importer_page()
    {
        $practice  = Practice::first();
        $careCoach = $this->createUser($practice->id, 'care-center');

        $this->assertTrue($careCoach->hasPermission('ccd-import'));
    }
}
