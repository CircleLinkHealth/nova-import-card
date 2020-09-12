<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Traits\Tests\PostmarkCallbackHelpers;
use App\Traits\Tests\PracticeHelpers;
use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Seeder;

class CreateInboundPostmarkCallbacks extends Seeder
{
    use PostmarkCallbackHelpers;
    use PracticeHelpers;
    use UserHelpers;
    private \CircleLinkHealth\Customer\Entities\User $careAmbassador;

    private \CircleLinkHealth\Customer\Entities\Practice $practice;

    public function __construct()
    {
        $this->practice       = $this->setupPractice();
        $this->careAmbassador = $this->createUser($this->practice->id, 'care-ambassador');
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $x = $this->createPatientData(Enrollee::ENROLLED);
        $x = 1;
    }
}
