<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Calls;

use CircleLinkHealth\SharedModels\Entities\Call;
use Carbon\Carbon;

class NurseCallStatistics
{
    protected $endTime;
    protected $minCallsForAverageConsideration   = 0;
    protected $minMinutesForAverageConsideration = 0;
    protected $minutesPadding                    = 0;

    /**
     *  This algorithm helps determine the average velocity of placed by a nurse.
     */
    protected $nurses;
    protected $startTime;
    protected $systemTime;

    public function __construct(
        $nurses,
        Carbon $startRange,
        Carbon $endRange
    ) {
        $this->nurses    = $nurses;
        $this->startTime = $startRange; //->subMinutes($this->minutesPadding);
        $this->endTime   = $endRange; //->addMinutes($this->minutesPadding);
    }

    public function callsPerHour(Carbon $month)
    {
    }

    public function nurseCallsPerHourAggregated()
    {
        $results = [];

//        $this->nurses = [Nurse::find(5)];

        foreach ($this->nurses as $nurse) {
            $differentiations = 10;
            $name             = $nurse->user->getFullName();

            for ($i = 0; $i < $differentiations; ++$i) {
                $rangeStart = Carbon::parse($this->startTime)->addHours($i);
                $rangeEnd   = Carbon::parse($this->endTime)->addHours($i);

                $calls[$name][$i] = Call::where('outbound_cpm_id', $nurse->user->id)
                    ->where('called_date', '>=', $rangeStart->toDateTimeString())
                    ->where('called_date', '<=', $rangeEnd->toDateTimeString());

                $callsForActivityPeriod = $calls[$name][$i];

                $count = $callsForActivityPeriod->get()->count();

//                if ($count < $this->minCallsForAverageConsideration) {
//                    continue 1;
//                }

                $adjustedRangeStart = Carbon::parse($callsForActivityPeriod->get()->sortBy('called_date')->first()['called_date']);
                $adjustedRangeEnd   = Carbon::parse($callsForActivityPeriod->get()->sortByDesc('called_date')->first()['called_date']);

                $activityHour = $adjustedRangeStart->diffInMinutes($adjustedRangeEnd);

                $success = $callsForActivityPeriod->where('status', 'reached')->count();

//                if ($activityHour < $this->minMinutesForAverageConsideration) {
//                    continue 1;
//                }

                $results[$name][$i]['Success %'] = (0 != $success)
                    ? round(($success / $count) * 100, 2).'%'
                    : 'N/A';

                $results[$name][$i]['Total Calls For Activity Period'] = $count;

                $results[$name][$i]['Active Period Start'] = $adjustedRangeStart->format('H:i');
                $results[$name][$i]['Active Period End']   = $adjustedRangeEnd->format('H:i');

                $results[$name][$i]['Activity Adjusted Hour'] = $activityHour.' mins';

                if (0 != $activityHour && 0 != $count) {
                    $results[$name][$i]['Adjusted Calls Per Hour'] = round(($count / $activityHour) * 60, 1);
                }
            }

//             if(isset($results[$name])){
//                 $results[$name]['Average CPH For Period'] = collect($results[$name])->average('Adjusted Calls Per Hour');
//             };
        }

        return [$this->startTime->toDateString() => $results];
    }
}
