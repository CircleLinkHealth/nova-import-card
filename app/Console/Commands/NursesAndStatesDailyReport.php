<?php

namespace App\Console\Commands;

use App\SaasAccount;
use App\Services\NursesAndStatesDailyReportService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NursesAndStatesDailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:nursesAndStatesDaily {forDate?}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uploads data to S3';
    private $service;

    /**
     * Create a new command instance.
     *
     * @param NursesAndStatesDailyReportService $service
     */
    public function __construct(NursesAndStatesDailyReportService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = $this->argument('forDate');
        if ($date) {
            try {
                $date = Carbon::parse($date);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                die(1);
            }
        } else {
            $date = Carbon::today()->subDay(1)->startOfDay();
        }

        $data = $this->service->collectData($date);

        /*$data = [];
        User::ofType('care-center')
            ->with([
                'nurseInfo.windows',
                'pageTimersAsProvider' => function ($q) use ($date) {
                    $q->where([
                        ['start_time', '>=', $date->copy()->startOfDay()->toDateTimeString()],
                        ['end_time', '<=', $date->copy()->endOfDay()->toDateTimeString()],
                    ]);
                },
                'outboundCalls'        => function ($q) use ($date) {
                    $q->where('scheduled_date', $date->toDateString())
                      ->orWhere('called_date', '>=', $date->toDateTimeString())
                      ->where('called_date', '<=',
                          $date->copy()->endOfDay()->toDateTimeString());
                },
            ])
            ->whereHas('outboundCalls', function ($q) use ($date) {
                $q->where('scheduled_date', $date->toDateString())
                  ->orWhere('called_date', '>=', $date->toDateTimeString());
            })
            ->chunk(10, function ($nurses) use (&$data, $date) {
                foreach ($nurses as $nurse) {
                    $data[] = collect([
                        //changed to user id
                        'nurse_id'        => $nurse->id,
                        'nurse_full_name' => $nurse->getFullName(),
                        //                        'name'           => $nurse->first_name,
                        //                        'last_name'      => $nurse->last_name,
                        'actualHours'     => $nurse->pageTimersAsProvider->sum('billable_duration'),
                        'committedHours'  => $nurse->nurseInfo->windows->where('day_of_week',
                            carbonToClhDayOfWeek($date->dayOfWeek))->sum(function ($window) {
                            return $window->numberOfHoursCommitted();
                        }),
                        'scheduledCalls'  => $nurse->outboundCalls->where('status', 'scheduled')->count(),
                        'actualCalls'     => $nurse->outboundCalls->whereIn('status',
                            ['reached', 'not reached', 'dropped'])->count(),
                        'successful'      => $nurse->outboundCalls->where('status', 'reached')->count(),
                        'unsuccessful'    => $nurse->outboundCalls->whereIn('status',
                            ['not reached', 'dropped'])->count(),
                    ]);
                }
            });*/

        $fileName = "nurses-and-states-daily-report-{$date->toDateString()}.json";
        $path     = storage_path($fileName);
        $saved    = file_put_contents($path, json_encode($data));

        if ( ! $saved) {
            if (app()->environment('worker')) {
                sendSlackMessage(
                    '#callcenter_ops',
                    "Nurses weekly calls and work hours report {$date->toDateString()} could not be created. \n"
                );
            }
        }
        SaasAccount::whereSlug('circlelink-health')
                   ->first()
                   ->addMedia($path)
                   ->toMediaCollection($fileName);

        if (app()->environment('worker')) {
            sendSlackMessage(
                '#callcenter_ops',
                "Nurses weekly calls and work hours report {$date->toDateString()} created. \n"
            );
        }

        $this->info('Daily Nurses Calls & Work hrs uploaded to S3');
    }

}
