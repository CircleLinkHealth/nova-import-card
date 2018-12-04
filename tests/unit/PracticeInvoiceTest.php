<?php

namespace Tests\Unit;

use App\User;
use App\Practice;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PracticeInvoiceTest extends TestCase
{
    private $patient;
    private $practice;
    private $date;


    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_a_practice_has_a_chargeable_service()
    {
        $this->assertTrue(true);
    }




    public function setUp()
    {
        parent::setUp();

        $this->patient  = factory(User::class)->create();
        $this->practice  = factory(Practice::class)->create();
        $this->date  = new Carbon('2017-08-01');
    }
}
