<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Jobs;

use CircleLinkHealth\Core\Entities\DatabaseNotification;

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
            ::where('data->status->twilio->sid', '=', $this->sid)
                ->where('data->status->twilio->accountSid', '=', $this->accountSid)
                ->first();
    }
}
