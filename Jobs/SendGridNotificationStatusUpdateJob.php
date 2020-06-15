<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Jobs;

use CircleLinkHealth\Core\Entities\DatabaseNotification;

class SendGridNotificationStatusUpdateJob extends NotificationStatusUpdateJob
{
    /**
     * @var string
     */
    protected $smtpId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $smtpId, array $props)
    {
        parent::__construct(null, 'mail', $props);
        $this->smtpId = $smtpId;
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
        return "smtp-id[$this->smtpId]";
    }

    protected function getNotification()
    {
        return DatabaseNotification
            ::where('data->status->mail->smtp_id', '=', $this->smtpId)
                ->first();
    }
}
