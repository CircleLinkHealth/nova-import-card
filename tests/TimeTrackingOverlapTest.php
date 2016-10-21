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
                $startTime->toDateTimeString(),
                $endTime->toDateTimeString(),
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->subSeconds(10)->toDateTimeString(),
                $endTime->copy()->addSeconds(10)->toDateTimeString(),
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->addSeconds(10)->toDateTimeString(),
                $endTime->copy()->addSeconds(60)->toDateTimeString(),
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->subSeconds(30)->toDateTimeString(),
                $endTime->toDateTimeString(),
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->addSeconds(5)->toDateTimeString(),
                $endTime->copy()->subSeconds(5)->toDateTimeString(),
                $startTime->diffInSeconds($endTime),
            ],
        ]);

        $this->createActivitiesAndRunTest($create, $startTime);

    }


    /**
     *      --------------------
     *          -----------------------------
     *    -----------------------------
     * -------------------------
     *           -----
     */
    public function testCase2()
    {
        $startTime = Carbon::now();
        $endTime = $startTime->copy()->addSeconds(30);

        $create = new Collection([
            [
                $startTime->toDateTimeString(),
                $endTime->toDateTimeString(),
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->addSeconds(20)->toDateTimeString(),
                $endTime->copy()->addSeconds(60)->toDateTimeString(),
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->subSeconds(10)->toDateTimeString(),
                $endTime->copy()->addSeconds(10)->toDateTimeString(),
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->subSeconds(30)->toDateTimeString(),
                $endTime->toDateTimeString(),
                $startTime->diffInSeconds($endTime),
            ],
            [
                $startTime->copy()->addSeconds(5)->toDateTimeString(),
                $endTime->copy()->subSeconds(5)->toDateTimeString(),
                $startTime->diffInSeconds($endTime),
            ],
        ]);

        $this->createActivitiesAndRunTest($create, $startTime);

    }
}
