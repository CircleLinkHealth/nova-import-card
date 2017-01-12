<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 21/10/16
 * Time: 4:02 AM
 */

namespace Tests\Helpers;

use App\Activity;
use App\NurseMonthlySummary;
use App\PageTimer;
use App\Patient;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

trait TimeTrackingHelpers
{
    public function createActivitiesAndRunTest(
        Collection $create
    ) {
        foreach ($create->all() as $new) {
            $this->createTrackingEvent(
                $this->provider,
                $this->patient,
                $this->programId,
                $new[2],
                $new[0],
                $new[1],
                $new[3] ?? null
            );

            $this->seeInDatabase('lv_page_timer', [
                'actual_start_time' => $new[0],
                'actual_end_time'   => $new[1],
            ]);
        }

        $minStartDate = Carbon::parse($create->min(0));
        $maxEndDate = Carbon::parse($create->max(1));

        $pageTimers = PageTimer::where('actual_start_time', '>=', $minStartDate)
            ->where('patient_id', $this->patient->id)
            ->orderBy('id', 'desc')
            ->take($create->count())
            ->get();

        $sum = $pageTimers->sum('billable_duration');

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
        int $durationInSeconds,
        Carbon $startTime,
        Carbon $testEndTime,
        $activity = 'Patient Overview Review'
    ) {
        if (empty($activity)) {
            $activity = 'Patient Overview Review';
        }

        $response = $this->call('POST', route('api.pagetracking'), [
            'patientId'        => $patient->id,
            'providerId'       => $provider->id,
            'totalTime'        => $durationInSeconds * 1000,
            'programId'        => $programId,
            'startTime'        => $startTime->toDateTimeString(),
            'testEndTime'      => $testEndTime->toDateTimeString(),
            'urlFull'          => 'www.url.com',
            'urlShort'         => 'url.com',
            'ipAddr'           => '1.1.1.1',
            'activity'         => $activity,
            'title'            => 'title',
            'redirectLocation' => 'redirect',
        ]);

        $this->seeInDatabase('lv_page_timer', [
            'patient_id'        => $patient->id,
            'provider_id'       => $provider->id,
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

    /**
     * @param User $patient
     * @param User $nurse
     * @param $duration
     *
     * @return Activity
     */
    public function createActivityForPatientNurse(Patient $patient, User $nurse, $duration) : Activity {

        //since this doesn't happen automatically, for testing purposes we update the patient's
        //current ccm_time


        $patient->cur_month_activity_time = $patient->cur_month_activity_time + $duration;
        $patient->save();

        return Activity::create([

            'duration' => $duration,
            'duration_unit' => 'seconds',
            'patient_id' => $patient->user_id,
            'provider_id' => $nurse->id,
            'type' => 'Test Activity'

        ]);

    }
}