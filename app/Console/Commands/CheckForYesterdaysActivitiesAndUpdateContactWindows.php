<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\NurseContactWindow;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Console\Command;

class CheckForYesterdaysActivitiesAndUpdateContactWindows extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:checkForYesterdaysActivitiesForCalendarEvents {forDate?}';

    /**
     * Create a new command instance.
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
        $date = $this->argument('forDate') ?? null;

        $dateToCheck = ! empty($date)
            ? Carbon::parse($date)
            : Carbon::parse(now())->startOfDay()->copy()->subDay(1);

        NurseContactWindow::with('nurse')
            ->where('date', $dateToCheck)
            ->whereHas('nurse', function ($q) {
                $q->where('status', 'active');
            })->chunkById(100, function ($windows) use ($dateToCheck) {
                collect($windows)->map(function ($window) use ($dateToCheck) {
                    $userId = $window->nurse->user_id;
                    $date = Carbon::parse($dateToCheck)->toDateTimeString();
                    //@todo: We need to check if all hrs commited are worked and not just if nurse worked.
                    $activitiesExist = PageTimer::where('provider_id', $userId)
                        ->where([
                            ['start_time', '>=', Carbon::parse($date)->startOfDay()],
                            ['start_time', '<=', Carbon::parse($date)->endOfDay()],
                        ])->exists();

                    $validated = 'worked';
                    if (true !== $activitiesExist) {
                        $validated = 'not_worked';
                    }

                    $window->update([
                        'validated'  => $validated,
                        'updated_at' => Carbon::parse(now())->toDateTimeString(),
                    ]);
                });
            });

        return info('Success');
    }
}
