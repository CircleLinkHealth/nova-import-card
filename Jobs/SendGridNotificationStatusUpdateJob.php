<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Jobs;

use CircleLinkHealth\Core\Entities\DatabaseNotification;

class SendGridNotificationStatusUpdateJob extends NotificationStatusUpdateJob
{
    /**
     * @var string could be smtp-id or sg_message_id
     */
    protected $id;

    /** @var bool */
    protected $isSgMessageId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $id, bool $isSgMessageId, array $props)
    {
        parent::__construct(null, 'mail', $props);
        $this->id            = $id;
        $this->isSgMessageId = $isSgMessageId;
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
        return $this->isSgMessageId ? "sg_message_id[$this->id]" : "smtp-id[$this->id]";
    }

    protected function getNotification()
    {
        return ($this->isSgMessageId ? DatabaseNotification::where('data->status->mail->sg_message_id', '=', $this->id) :
            DatabaseNotification::where('data->status->mail->smtp_id', '=', $this->id))->first();
    }
}
