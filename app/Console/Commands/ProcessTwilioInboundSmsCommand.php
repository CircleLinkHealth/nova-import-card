<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\ProcessTwilioInboundSmsJob;
use App\TwilioInboundSms;
use Illuminate\Console\Command;

class ProcessTwilioInboundSmsCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process inbound sms again';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:twilio-inbound-sms {recordId}';

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
        $item = TwilioInboundSms::findOrFail($this->argument('recordId'));
        ProcessTwilioInboundSmsJob::dispatch([
            'From' => $item->from,
            'Body' => $item->body,
        ], $item->id);

        return 0;
    }
}
