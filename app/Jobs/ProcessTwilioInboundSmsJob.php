<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\TwilioInboundSms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTwilioInboundSmsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var array
     */
    private $input;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $input)
    {
        $this->input = $input;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 0. store request data in twilio_inbound_sms
        $this->storeRawLogs();
        // 1. read source number, find patient
        // 2. parse input
        // 3. create call for nurse with ASAP flag
    }

    private function storeRawLogs(): void
    {
        try {
            TwilioInboundSms::create([
                'data' => json_encode($this->input),
            ]);
        } catch (\Throwable $e) {
            Log::warning($e->getMessage());
        }
    }
}
