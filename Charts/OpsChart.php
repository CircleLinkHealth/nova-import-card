<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Charts;

use Carbon\CarbonPeriod;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use ConsoleTVs\Charts\Classes\Chartjs\Chart;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class OpsChart extends Chart
{
    const ADMIN_CHART_CACHE_KEY                  = 'chart:clh:total_billable_patients';
    const AUTO_ENROLMENT_INVITES_CHART_CACHE_KEY = 'chart:clh:auto_enrollment_invites';

    /**
     * Initializes the chart.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Incomplete!
     *
     * @return mixed
     */
    public static function autoEnrollmentChart(int $practiceId)
    {
        return Cache::remember(
            self::AUTO_ENROLMENT_INVITES_CHART_CACHE_KEY,
            10,
            function () use ($practiceId) {
                $period = CarbonPeriod::create(now()->subWeeks(2), now());

                Enrollee::where('practice_id', $practiceId)->where('status', Enrollee::ENROLLED)->where('auto_enrollment_triggered', true);

                foreach ($period as $date) {
                }
            }
        );
    }

    public static function clearClhCachedChart()
    {
    }

    public static function clhGrowthChart()
    {
        $clh = SaasAccount::whereSlug('circlelink-health')->first();

        if ( ! $clh) {
            return new static();
        }

        return Cache::remember(
            self::ADMIN_CHART_CACHE_KEY,
            1440,
            function () use ($clh) {
                $period = CarbonPeriod::create(now()->subMonths(2), now());
                $collections = [];

                foreach ($period as $date) {
                    $collections[] = "ops-daily-report-{$date->toDateString()}.json";
                }

                $dataset = collect();

                Media::whereModelType(SaasAccount::class)->whereIn('collection_name', $collections)->orderByDesc(
                    'id'
                )->chunkById(
                    50,
                    function (Collection $medias) use (&$dataset) {
                        $medias->each(
                            function (Media $media) use (&$dataset) {
                                $json = $media->getFile();

                                //first check if we have a valid file
                                if ( ! $json) {
                                    return [];
                                }
                                //then check if it's in json format
                                if ( ! is_json($json)) {
                                    throw new \Exception('File retrieved is not in json format.', 500);
                                }

                                $decoded = json_decode($json, true);
                                $clhTotals = $decoded['rows']['CircleLink Total'] ?? [];

                                $dataset[] = [
                                    'Added'         => $clhTotals['Added'] ?? null,
                                    'Paused'        => $clhTotals['Paused'] ?? null,
                                    'Unreachable'   => $clhTotals['Unreachable'] ?? null,
                                    'Withdrawn'     => $clhTotals['Withdrawn'] ?? null,
                                    '0 mins'        => $clhTotals['0 mins'] ?? null,
                                    '0-5'           => $clhTotals['0-5'] ?? null,
                                    '5-10'          => $clhTotals['5-10'] ?? null,
                                    '10-15'         => $clhTotals['10-15'] ?? null,
                                    '15-20'         => $clhTotals['15-20'] ?? null,
                                    '20+'           => $clhTotals['20+'] + $clhTotals['20+ BHI'] ?? null,
                                    'total'         => $clhTotals['Total'] ?? null,
                                    'dateGenerated' => $decoded['dateGenerated'] ?? null,
                                ];
                            }
                        );
                    }
                );

                $chart = new static();
                $chart->labels($dataset->pluck('dateGenerated')->all());
                $chart->dataset('Added', 'line', $dataset->pluck('Added')->all())
                    ->options(
                        [
                            'hidden'          => true,
                            'fill'            => false,
                            'backgroundColor' => '#00ffcc',
                            'color'           => '#00ffcc',
                        ]
                    );
                $chart->dataset('Paused', 'line', $dataset->pluck('Paused')->all())->options(
                    [
                        'hidden'          => true,
                        'fill'            => false,
                        'backgroundColor' => 'rgba(63, 166, 206, 0.88)',
                        'color'           => 'rgba(63, 166, 206, 0.88)',
                    ]
                );
                $chart->dataset('Unreachable', 'line', $dataset->pluck('Unreachable')->all())->options(
                    [
                        'hidden'          => true,
                        'fill'            => false,
                        'backgroundColor' => 'ff0000',
                        'color'           => 'ff0000',
                    ]
                );
                $chart->dataset('Withdrawn', 'line', $dataset->pluck('Withdrawn')->all())->options(
                    [
                        'hidden'          => true,
                        'fill'            => false,
                        'backgroundColor' => 'rgb(255, 2, 2)',
                        'color'           => 'rgb(255, 2, 2)',
                    ]
                );
                $chart->dataset('0 mins', 'line', $dataset->pluck('0 mins')->all())->options(
                    [
                        'hidden'          => true,
                        'fill'            => true,
                        'backgroundColor' => 'rgba(238, 238, 238, 0.58)',
                        'color'           => 'rgba(238, 238, 238, 0.58)',
                    ]
                );
                $chart->dataset('0-5', 'line', $dataset->pluck('0-5')->all())->options(
                    [
                        'hidden'          => true,
                        'fill'            => false,
                        'backgroundColor' => 'rgba(238, 238, 238, 0.58)',
                        'color'           => 'rgba(238, 238, 238, 0.58)',
                    ]
                );
                $chart->dataset('5-10', 'line', $dataset->pluck('5-10')->all())->options(
                    [
                        'hidden'          => true,
                        'fill'            => false,
                        'backgroundColor' => 'rgba(120, 120, 120, 0.58)',
                        'color'           => 'rgba(120, 120, 120, 0.58)',
                    ]
                );
                $chart->dataset('10-15', 'line', $dataset->pluck('10-15')->all())->options(
                    [
                        'hidden'          => true,
                        'fill'            => false,
                        'backgroundColor' => '#ffff00',
                        'color'           => '#ffff00',
                    ]
                );
                $chart->dataset('15-20', 'line', $dataset->pluck('15-20')->all())->options(
                    [
                        'hidden'          => true,
                        'fill'            => false,
                        'backgroundColor' => 'rgb(255, 2, 2)',
                        'color'           => 'rgb(255, 2, 2)',
                    ]
                );
                $chart->dataset('20+ Mins, Any Code', 'line', $dataset->pluck('20+')->all())->options(
                    [
                        'hidden'          => false,
                        'fill'            => true,
                        'backgroundColor' => '#8aed00',
                        'color'           => '#8aed00',
                    ]
                );
                $chart->dataset('Total Number Of Patients', 'line', $dataset->pluck('total')->all())->options(
                    [
                        'hidden'          => false,
                        'fill'            => false,
                        'backgroundColor' => '#179553',
                        'color'           => '#179553',
                    ]
                );

                return $chart;
            }
        );
    }
}
