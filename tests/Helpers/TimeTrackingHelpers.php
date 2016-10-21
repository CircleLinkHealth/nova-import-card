<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 21/10/16
 * Time: 4:02 AM
 */

namespace Tests\Helpers;

use App\Activity;
use App\PageTimer;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

trait TimeTrackingHelpers
{
    public function createActivitiesAndRunTest(
        Collection $create,
        Carbon $startTime
    ) {
        foreach ($create->all() as $new) {
            $this->createTrackingEvent(
                $this->provider,
                $this->patient,
                $this->programId,
                $new[2] * 1000,
                $new[0],
                $new[1]
            );

            $this->seeInDatabase('lv_page_timer', [
                'actual_start_time' => $new[0],
                'actual_end_time'   => $new[1],
            ]);
        }

        $pageTimers = PageTimer::where('created_at', '>=', $startTime)
            ->orderBy('id', 'desc')
            ->take($create->count())
            ->get();

        $sum = $pageTimers->sum('billable_duration');

        $minStartDate = Carbon::parse($create->min(0));
        $maxEndDate = Carbon::parse($create->max(1));

        $this->assertEquals($minStartDate->diffInSeconds($maxEndDate), $sum);

        $activities = Activity::whereIn('page_timer_id', $pageTimers->pluck('id')->all())
            ->get();

        $this->assertEquals($activities->sum('duration'),
            $pageTimers->whereIn('id', $activities->pluck('page_timer_id')->all())->sum('billable_duration'));
    }

    public function createTrackingEvent(
        User $provider,
        User $patient,
        int $programId,
        int $duration,
        Carbon $startTime,
        Carbon $testEndTime,
        $activity = 'Patient Overview Review'
    ) {
        $response = $this->call('POST', route('api.pagetracking'), [
            'patientId'   => $patient->ID,
            'providerId'  => $provider->ID,
            'totalTime'   => $duration,
            'programId'   => $programId,
            'startTime'   => $startTime->toDateTimeString(),
            'testEndTime' => $testEndTime->toDateTimeString(),
            'urlFull'     => 'www.url.com',
            'urlShort'    => 'url.com',
            'ipAddr'      => '1.1.1.1',
            'activity'    => $activity,
            'title'       => 'title',
        ]);

        $this->seeInDatabase('lv_page_timer', [
            'patient_id'        => $patient->ID,
            'provider_id'       => $provider->ID,
            'duration'          => $startTime->diffInSeconds($testEndTime),
            'program_id'        => $programId,
            'actual_start_time' => $startTime->toDateTimeString(),
            'actual_end_time'   => $testEndTime->toDateTimeString(),
            'url_full'          => 'www.url.com',
            'url_short'         => 'url.com',
            'ip_addr'           => '1.1.1.1',
            'activity_type'     => $activity,
            'title'             => 'title',
        ]);

        $this->assertResponseStatus(201);
    }
}