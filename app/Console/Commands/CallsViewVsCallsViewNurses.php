<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\SharedModels\Entities\CallView;
use App\CallViewNurses;
use Illuminate\Console\Command;

class CallsViewVsCallsViewNurses extends Command
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
    protected $signature = 'calls:view-performance';

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
        /*
         select * from `calls_view_nurses` where `nurse_id` = 13247 and `status` = 'scheduled'
        order by asap desc, FIELD(type, "Call Back") desc, scheduled_date asc, call_time_start asc, call_time_end asc
         */
        $nurseId = 13247;

        $start = microtime(true) * 1000;
        for ($i = 0; $i < 100; ++$i) {
            $query = CallViewNurses::where('nurse_id', '=', $nurseId);

            $query->where('status', '=', 'scheduled')
                ->orderByRaw('asap desc, FIELD(type, "Call Back") desc, scheduled_date asc, call_time_start asc, call_time_end asc')
                ->get();
        }
        $time = microtime(true) * 1000 - $start;
        $avg  = round($time / 100, 2);

        $start = microtime(true) * 1000;
        for ($i = 0; $i < 100; ++$i) {
            $query = CallView::where('nurse_id', '=', $nurseId);

            $query->where('status', '=', 'scheduled')
                ->orderByRaw('asap desc, FIELD(type, "Call Back") desc, scheduled_date asc, call_time_start asc, call_time_end asc')
                ->get();
        }
        $time2 = microtime(true) * 1000 - $start;
        $avg2  = round($time2 / 100, 2);

        $this->info("New: {$avg}ms, Old: {$avg2}ms");
    }
}
