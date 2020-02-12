<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Charts;

use App\Constants;
use Carbon\CarbonPeriod;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use ConsoleTVs\Charts\Classes\Chartjs\Chart;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class TotalBillablePatients extends Chart
{
    const ADMIN_CHART_CACHE_KEY           = 'chart:clh:total_billable_patients';
    
    /**
     * Initializes the chart.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    public static function clhGrowthChart()
    {
        $clh = SaasAccount::whereSlug('circlelink-health')->first();
        
        if ( ! $clh) {
            return new static();
        }
        
        return Cache::remember(
            self::ADMIN_CHART_CACHE_KEY,
            1,
            function () use ($clh) {
                $period      = CarbonPeriod::create(now()->subMonths(3), now());
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
                                
                                $decoded   = json_decode($json, true);
                                $clhTotals = $decoded['rows']['CircleLink Total'] ?? [];
                                
                                $dataset[] = [
                                    'Added'         => $clhTotals['Added'] ?? null,
                                    'Paused'        => $clhTotals['Paused'] ?? null,
                                    'Unreachable'   => $clhTotals['Unreachable'] ?? null,
                                    'Withdrawn'     => $clhTotals['Withdrawn'] ?? null,
                                    '0 mins'           => $clhTotals['0 mins'] ?? null,
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
                $chart->dataset('Added', 'line', $dataset->pluck('Added')->all())->backgroundColor('#00ffcc')->fill(false)->options(['hidden' => true]);
                $chart->dataset('Paused', 'line', $dataset->pluck('Paused')->all())->backgroundColor('#ff0000')->fill(false)->options(['hidden' => true]);
                $chart->dataset('Unreachable', 'line', $dataset->pluck('Unreachable')->all())->backgroundColor('#ff0000')->fill(false)->options(['hidden' => true]);
                $chart->dataset('Withdrawn', 'line', $dataset->pluck('Withdrawn')->all())->backgroundColor('#ff0000')->fill(false)->options(['hidden' => true]);
                $chart->dataset('0 mins', 'line', $dataset->pluck('0 mins')->all())->options([
                    'hidden' => true,
                    'fill' => true,
                    'backgroundColor' => 'rgba(238, 238, 238, 0.58)',
                                                                                                                                     ]);
                $chart->dataset('0-5', 'line', $dataset->pluck('0-5')->all())->backgroundColor('#ffff00')->fill(false)->options(['hidden' => true]);
                $chart->dataset('5-10', 'line', $dataset->pluck('5-10')->all())->backgroundColor('#ffff00')->fill(false)->options(['hidden' => true]);
                $chart->dataset('10-15', 'line', $dataset->pluck('10-15')->all())->backgroundColor('#ffff00')->fill(false)->options(['hidden' => true]);
                $chart->dataset('15-20', 'line', $dataset->pluck('15-20')->all())->backgroundColor('#ffff00')->fill(false)->options(['hidden' => true]);
                $chart->dataset('20+ Mins, Any Code', 'line', $dataset->pluck('20+')->all())->backgroundColor(
                    '#8aed00'
                )->fill(true);
                $chart->dataset('Total Number Of Patients', 'line', $dataset->pluck('total')->all())->backgroundColor(
                    '#179553'
                )->fill(false);
                
                return $chart;
            }
        );
    }
    
    public static function clearClhCachedChart() {
    
    }
}
