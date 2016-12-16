<?php

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\Helpers\CarePlanHelpers;
use Tests\Helpers\TimeTrackingHelpers;
use Tests\Helpers\UserHelpers;

class TimeTrackingEdgeCasesTest extends TestCase
{
    use CarePlanHelpers,
        UserHelpers,
        TimeTrackingHelpers;

    protected $programId;
    protected $provider;
    protected $patient;

    public function setUp()
    {
        parent::setUp();

        $this->programId = 9;
        $this->provider = $this->createUser();
        $this->patient = $this->createUser($this->programId, 'participant');
    }

    public function testNonCcmSecondaryOverlapsGreedy()
    {
        //Add some time so that it won't mess with the values of the previous test
        $startTime = Carbon::now()->addSeconds(10);
        $endTime = $startTime->copy()->addSeconds(60);

        $create = new Collection([
            [
                $startTime->copy()->subMinutes(2),
                $endTime->copy()->addMinutes(2),
                $startTime->copy()->subMinutes(2)->diffInSeconds($endTime),
                'non ccm',
            ],
            [
                $startTime,
                $endTime,
                $startTime->diffInSeconds($endTime),
                'non ccm',
            ],
        ]);

        $this->createActivitiesAndRunTest($create);
    }
}
