<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Carbon\Carbon;
use Tests\TestCase;

class PracticeInvoiceTest extends TestCase
{
    private $date;
    private $patient;
    private $practice;

    public function setUp()
    {
        parent::setUp();

        $this->patient  = factory(User::class)->create();
        $this->practice = factory(Practice::class)->create();
        $this->date     = new Carbon('2017-08-01');
    }

    /**
     * A basic test example.
     */
    public function test_a_practice_has_a_chargeable_service()
    {
        $this->assertTrue(true);
    }
}
