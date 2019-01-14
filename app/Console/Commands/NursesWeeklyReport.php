<?php

namespace App\Console\Commands;

use App\SaasAccount;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NursesWeeklyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nurse:save';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uploads data to S3';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
       $date=  Carbon::now();
       $data   = [];
        $nurses = User::ofType('care-center')
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
                                ->where('called_date', '<=', $date->copy()->endOfDay()->toDateTimeString());
                          },
                      ])->whereHas('outboundCalls', function ($q) use ($date) {
                $q->where('scheduled_date', $date->toDateString())
                  ->orWhere('called_date', '>=', $date->toDateTimeString());
            })->chunk(10, function ($nurses) use (&$data, $date) {
                foreach ($nurses as $nurse) {
                    $data[] = [
                        'nurse_info_id'  => $nurse->nurseInfo->id,
                        'name'           => $nurse->first_name,
                        'last_name'      => $nurse->last_name,
                        'actualHours'    => $nurse->pageTimersAsProvider->sum('billable_duration'),
                        'committedHours' => $nurse->nurseInfo->windows->where('day_of_week',
                            carbonToClhDayOfWeek($date->dayOfWeek))->sum(function ($window) {
                            return $window->numberOfHoursCommitted();
                        }),
                        'scheduledCalls' => $nurse->outboundCalls->where('status', 'scheduled')->count(),
                        'actualCalls'    => $nurse->outboundCalls->whereIn('status',
                            ['reached', 'not reached', 'dropped'])->count(),
                        'successful'     => $nurse->outboundCalls->where('status', 'reached')->count(),
                        'unsuccessful'   => $nurse->outboundCalls->whereIn('status',
                            ['not reached', 'dropped'])->count(),
                    ];
                }
            });

        $path  = storage_path("nurses-weekly-report-{$date->toDateString()}.json");
        $saved = file_put_contents($path, json_encode($data));

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
                   ->toMediaCollection("nurses-weekly-report-{$date->toDateString()}.json");

        if (app()->environment('worker')) {
            sendSlackMessage(
                '#callcenter_ops',
                "Nurses weekly calls and work hours report {$date->toDateString()} created. \n"
            );
        }

        return info('Daily Nurses Calls & Work hrs uploaded to S3');
    }
}
