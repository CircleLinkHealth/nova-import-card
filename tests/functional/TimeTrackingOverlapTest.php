<?php


use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\Helpers\HandlesUsersAndCarePlans;
use Tests\Helpers\TimeTrackingHelpers;

class TimeTrackingOverlapTest extends TestCase
{
    use HandlesUsersAndCarePlans, TimeTrackingHelpers;

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

    /**
     *      --------------------
     *    -----------------------------
     *          -----------------------------
     * -------------------------
     *           -----
     */
    public function testCase1()
    {
        $startTime = Carbon::now();
        $endTime = $startTime->copy()->addSeconds(30);

        $create = new Collection([
            [
                $startTime,
                $endTime,
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->subSeconds(10),
                $endTime->copy()->addSeconds(10),
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->addSeconds(10),
                $endTime->copy()->addSeconds(60),
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->subSeconds(30),
                $endTime,
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->addSeconds(5),
                $endTime->copy()->subSeconds(5),
                $startTime->diffInSeconds($endTime),
            ],
        ]);

        $this->createActivitiesAndRunTest($create, $startTime);

    }


    /**
     *  Really crazy case
     */
    public function testCrayCase1()
    {
        $startTime = Carbon::now();
        $endTime = $startTime->copy()->addSeconds(30);

        $create = new Collection([
            [
                $startTime,
                $endTime,
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->subSeconds(30),
                $endTime,
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->addSeconds(5),
                $endTime->copy()->subSeconds(5),
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->addSeconds(20),
                $endTime->copy()->addSeconds(60),
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->addSeconds(1),
                $endTime->copy()->subSeconds(3),
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->subSeconds(10),
                $endTime->copy()->addSeconds(10),
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->subSeconds(1),
                $endTime->copy()->addSeconds(75),
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->subSeconds(50),
                $endTime,
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime,
                $endTime,
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->addSeconds(20),
                $endTime->copy()->addSeconds(60),
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->subSeconds(10),
                $endTime->copy()->addSeconds(10),
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->addSeconds(10),
                $endTime->copy()->addSeconds(60),
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->subSeconds(30),
                $endTime,
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->addSeconds(5),
                $endTime->copy()->subSeconds(5),
                $startTime->diffInSeconds($endTime),
            ],
        ]);

        $this->createActivitiesAndRunTest($create, $startTime);
    }
}
