<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwilioIntegration\Commands;

use CircleLinkHealth\TwilioIntegration\Services\TwilioInterface;
use Illuminate\Console\Command;

class LookupNumberCommand extends Command
{
    public TwilioInterface $twilio;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Use Twilio Lookup API to learn more information about a phone number.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lookup:number {e164PhoneNumber}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TwilioInterface $twilioClientService)
    {
        parent::__construct();
        $this->twilio = $twilioClientService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $number = $this->argument('e164PhoneNumber');

        $result   = $this->twilio->lookup($number);
        $isMobile = $result->isMobile ? 'Yes' : 'No';

        $this->info("Phone number: $result->phoneNumber");
        $this->info("Mobile: $isMobile");
        $this->info("Carrier: $result->carrierName");
        $this->error("ErrorCode: $result->errorCode");
        $this->error("ErrorDetails: $result->errorDetails");

        return 0;
    }
}
