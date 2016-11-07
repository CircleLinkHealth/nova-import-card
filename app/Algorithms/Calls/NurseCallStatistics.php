<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 10/5/16
 * Time: 12:54 PM
 */

namespace App\Algorithms\Calls;


use App\Call;
use Carbon\Carbon;

class NurseCallStatistics
{

    /**
     *  This algorithm helps determine the average velocity of placed by a nurse
     */

    protected $nurses;
    protected $systemTime;
    protected $startTime;
    protected $endTime;
    protected $minutesPadding = 5;
    protected $minCallsForAverageConsideration = 6;
    protected $minMinutesForAverageConsideration = 30;

    public function __construct( $nurses, Carbon $startRange, Carbon $endRange)
    {
        
        $this->nurses = $nurses;
        $this->startTime = $startRange; //->subMinutes($this->minutesPadding);
        $this->endTime = $endRange;//->addMinutes($this->minutesPadding);

    }

    public function nurseCallsPerHourAggregated(){

        $results = [];

//        $this->nurses = [NurseInfo::find(5)];

        foreach ($this->nurses as $nurse) {

            $differentiations = 10;
            $name = $nurse->user->fullName;

            for ($i = 0; $i < $differentiations; $i++) {

                $rangeStart = Carbon::parse($this->startTime)->addHours($i);
                $rangeEnd = Carbon::parse($this->endTime)->addHours($i);

                $calls[$name][$i] = Call::where('outbound_cpm_id', $nurse->user->id)
                    ->where('called_date', '>=', $rangeStart->toDateTimeString())
                    ->where('called_date', '<=', $rangeEnd->toDateTimeString());

                $callsForActivityPeriod = $calls[$name][$i];

                $count = $callsForActivityPeriod->get()->count();

//                if ($count < $this->minCallsForAverageConsideration) {
//                    continue 1;
//                }

                $adjustedRangeStart = Carbon::parse($callsForActivityPeriod->get()->sortBy('called_date')->first()['called_date']);
                $adjustedRangeEnd = Carbon::parse($callsForActivityPeriod->get()->sortByDesc('called_date')->first()['called_date']);

                $activityHour = $adjustedRangeStart->diffInMinutes($adjustedRangeEnd);

                $success = $callsForActivityPeriod->where('status', 'reached')->count();

//                if ($activityHour < $this->minMinutesForAverageConsideration) {
//                    continue 1;
//                }

                $results[$name][$i]['Success %'] = ($success != 0)
                    ? round(($success / $count) * 100, 2) . '%'
                    : 'N/A';

                $results[$name][$i]['Total Calls For Activity Period'] = $count;

                $results[$name][$i]['Active Period Start'] = $adjustedRangeStart->format('H:i');
                $results[$name][$i]['Active Period End'] = $adjustedRangeEnd->format('H:i');


                $results[$name][$i]['Activity Adjusted Hour'] = $activityHour . ' mins';

                if ($activityHour != 0 && $count != 0) {
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