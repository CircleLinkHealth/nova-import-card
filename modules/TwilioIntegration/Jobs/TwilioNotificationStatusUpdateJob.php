<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwilioIntegration\Jobs;

use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\Core\Jobs\NotificationStatusUpdateJob;

class TwilioNotificationStatusUpdateJob extends NotificationStatusUpdateJob
{
    /**
     * @var string
     */
    protected $accountSid;

    /**
     * @var string
     */
    protected $sid;

    /**
     * Create a new job instance.
     */
    public function __construct(string $sid = null, string $accountSid = null, array $props)
    {
        parent::__construct(null, 'twilio', $props);
        $this->sid        = $sid;
        $this->accountSid = $accountSid;
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     *
     * @return void
     */
    public function handle()
    {
        parent::handle();
    }

    protected function getIdentifier()
    {
        return "sid[$this->sid]|accountSid[$this->accountSid]";
    }

    protected function getNotification()
    {
        return DatabaseNotification
            ::where('twilio_sid', '=', $this->sid)
                ->where('twilio_account_sid', '=', $this->accountSid)
                ->first();
    }
}
