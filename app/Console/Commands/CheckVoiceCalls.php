<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Call;
use App\CpmCallAlert;
use CircleLinkHealth\Core\Entities\AppConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Nova;

class CheckVoiceCalls extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check voice calls and create alerts if needed';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:voice-calls {from} {to?}';

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
     * @return int
     */
    public function handle()
    {
        $from      = Carbon::parse($this->argument('from'));
        $to        = $this->argument('to') ?? now();
        $threshold = $this->getDurationThreshold();

        //1. should check for calls that are 'reached' and have short duration
        //2. should check for calls that are 'not reached' or 'ignored' and have long duration

        $callIds = collect();
        Call::with([
            'voiceCalls.voiceCallable',
        ])
            ->whereIn('status', [Call::REACHED, Call::NOT_REACHED, Call::IGNORED])
            ->whereDoesntHave('cpmCallAlert')
            ->whereHas('voiceCalls')
            ->whereBetween('called_date', [$from, $to])
            ->each(function ($item) use ($callIds, $threshold) {
                // we might have more than one voice call associated with a Call model.
                // if at least one of them is less than the threshold we will reach here
                // BUT, if at least one of them is higher than the threshold, then there is no reason to raise an alert

                foreach ($item->voiceCalls as $voiceCall) {
                    $duration = $voiceCall->voiceCallable->dial_conference_duration;
                    if (Call::REACHED === $item->status && $duration >= $threshold || Call::REACHED !== $item->status && $duration < $threshold) {
                        return;
                    }
                }

                $callIds->push(['call_id' => $item->id]);
            });

        if ($callIds->isNotEmpty()) {
            DB::table((new CpmCallAlert())->getTable())
                ->insert($callIds->toArray());

            $path = Nova::path().'/resources/cpm-call-alerts';
            sendSlackMessage('#carecoach_ops', "There are new call alerts pending. Please visit $path to resolve them.");
        }

        $count = $callIds->count();
        $this->info("Created $count alerts");

        return 0;
    }

    private function getDurationThreshold()
    {
        $default = 60 * 3; // 3 minutes
        $key     = 'voice_calls_alerts_threshold_seconds';

        return AppConfig::pull($key, $default);
    }
}
