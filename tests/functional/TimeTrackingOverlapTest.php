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
                $startTime->copy()->subSeconds(10)->diffInSeconds($endTime->copy()->addSeconds(10)),
            ],
            [
                $startTime->copy()->addSeconds(10),
                $endTime->copy()->addSeconds(60),
                $startTime->copy()->addSeconds(10)->diffInSeconds($endTime->copy()->addSeconds(60)),
            ],
            [
                $startTime->copy()->subSeconds(30),
                $endTime,
                $startTime->copy()->subSeconds(30)->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->addSeconds(5),
                $endTime->copy()->subSeconds(5),
                $startTime->copy()->addSeconds(5)->diffInSeconds($endTime->copy()->subSeconds(5)),
            ],
        ]);

        $this->createActivitiesAndRunTest($create);

    }


    /**
     *  Really crazy case
     */
    public function testCrayCase1()
    {
        //Add some time so that it won't mess with the values of the previous test
        $startTime = Carbon::now()->addMinutes(10);
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
                $startTime->copy()->subSeconds(30)->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->addSeconds(5),
                $endTime->copy()->subSeconds(5),
                $startTime->copy()->addSeconds(5)->diffInSeconds($endTime->copy()->subSeconds(5)),
            ],
            [
                $startTime->copy()->addSeconds(20),
                $endTime->copy()->addSeconds(60),
                $startTime->copy()->addSeconds(20)->diffInSeconds($endTime->copy()->addSeconds(60)),
            ],
            [
                $startTime->copy()->addSeconds(1),
                $endTime->copy()->subSeconds(3),
                $startTime->copy()->addSeconds(1)->diffInSeconds($endTime->copy()->subSeconds(3)),
            ],
            [
                $startTime->copy()->subSeconds(10),
                $endTime->copy()->addSeconds(10),
                $startTime->copy()->subSeconds(10)->diffInSeconds($endTime->copy()->addSeconds(10)),
            ],
            [
                $startTime->copy()->subSeconds(1),
                $endTime->copy()->addSeconds(75),
                $startTime->copy()->subSeconds(1)->diffInSeconds($endTime->copy()->addSeconds(75)),
            ],
            [
                $startTime->copy()->subSeconds(50),
                $endTime,
                $startTime->copy()->subSeconds(50)->diffInSeconds($endTime),
            ],
            [
                $startTime,
                $endTime,
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->addSeconds(20),
                $endTime->copy()->addSeconds(60),
                $startTime->copy()->addSeconds(20)->diffInSeconds($endTime->copy()->addSeconds(60)),
            ],
            [
                $startTime->copy()->subSeconds(10),
                $endTime->copy()->addSeconds(10),
                $startTime->copy()->subSeconds(10)->diffInSeconds($endTime->copy()->addSeconds(10)),
            ],
            [
                $startTime->copy()->addSeconds(10),
                $endTime->copy()->addSeconds(60),
                $startTime->copy()->addSeconds(10)->diffInSeconds($endTime->copy()->addSeconds(60)),
            ],
            [
                $startTime->copy()->subSeconds(30),
                $endTime,
                $startTime->copy()->subSeconds(30)->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->addSeconds(5),
                $endTime->copy()->subSeconds(5),
                $startTime->copy()->addSeconds(5)->diffInSeconds($endTime->copy()->subSeconds(5)),
            ],
        ]);

        $this->createActivitiesAndRunTest($create);
    }
}
