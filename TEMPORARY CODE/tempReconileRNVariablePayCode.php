<?php
$nurses = [1920, 1877];

$days = [ Carbon::parse('2017-02-01'),
          Carbon::parse('2017-02-02') ];

$activities = [];

foreach ($days as $day) {

    foreach ($nurses as $nurse) {

        $name = \App\User::find($nurse)->fullName;

        $patients =
            DB::table('lv_activities')
                ->where('provider_id', $nurse)
                ->distinct('patient_id')
                ->groupBy('patient_id')
                ->where('created_at', '>=', $day->startOfDay()->toDateTimeString())
                ->where('created_at', '<=', $day->endOfDay()->toDateTimeString())
                ->pluck('patient_id');

        $activities[$day->toDateString()][$name]['over'] = 0;
        $activities[$day->toDateString()][$name]['under'] = 0;

        foreach ($patients as $patient) {

            $count = 0;

            $ccm_that_far =
                \App\Activity
                    ::where('patient_id', $patient)
                    ->where('provider_id', $nurse)
                    ->where('created_at', '>=', $day->startOfDay()->toDateTimeString())
                    ->where('created_at', '<=', $day->endOfDay()->toDateTimeString())
                    ->sum('duration');

            $activities[$day->toDateString()][$name][$patient]['total'] = $ccm_that_far;

            if ($ccm_that_far > 1200) {
                $activities[$day->toDateString()][$name][$patient]['over'] = $ccm_that_far - 1200;
                $activities[$day->toDateString()][$name][$patient]['under'] = 1200;
            } else {
                $activities[$day->toDateString()][$name][$patient]['under'] = $ccm_that_far;
                $activities[$day->toDateString()][$name][$patient]['over'] = 0;
            }

            $activities[$day->toDateString()][$name]['over'] += $activities[$day->toDateString()][$name][$patient]['over'];
            $activities[$day->toDateString()][$name]['under'] += $activities[$day->toDateString()][$name][$patient]['under'];

        }

        $activities[$day->toDateString()][$name]['pagetimed'] =
            round(\App\PageTimer
                    ::where('provider_id', $nurse)
                    ->where('created_at', '>=', $day->startOfDay()->toDateTimeString())
                    ->where('created_at', '<=', $day->endOfDay()->toDateTimeString())
                    ->sum('duration') / 3600, 1);

        $activities[$day->toDateString()][$name]['over'] = round($activities[$day->toDateString()][$name]['over'] / 3600, 1);
        $activities[$day->toDateString()][$name]['under'] = round($activities[$day->toDateString()][$name]['under'] / 3600, 1);

    }
}