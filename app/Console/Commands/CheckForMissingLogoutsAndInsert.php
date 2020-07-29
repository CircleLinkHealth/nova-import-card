<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\CheckLogoutEventAndSave;
use App\LoginLogout;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckForMissingLogoutsAndInsert extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if user logout event was saved to DB and make stats to compare with users latest activity';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:checkForMissingLogoutsAndInsert {forDate?}';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $date = $this->argument('forDate') ?? null;

        if ($date) {
            $date = Carbon::parse($date);
        } else {
            $date = Carbon::yesterday();
        }

        LoginLogout::where([
            ['created_at', '>=', Carbon::parse($date)->startOfDay()],
            ['created_at', '<=', Carbon::parse($date)->endOfDay()],
        ])
            ->orderBy('created_at', 'asc')
            ->chunk(50, function ($yesterdaysActivities) use ($date) {
                foreach ($yesterdaysActivities as $loginActivity) {
                    CheckLogoutEventAndSave::dispatch($date, $loginActivity)->onQueue('low');
                }
            });
    }
}
