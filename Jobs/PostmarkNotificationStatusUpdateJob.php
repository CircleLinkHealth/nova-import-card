<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Jobs;

use CircleLinkHealth\Core\Entities\DatabaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PostmarkNotificationStatusUpdateJob extends NotificationStatusUpdateJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var string
     */
    protected $messageId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $messageId, array $props)
    {
        parent::__construct(null, 'mail', $props);
        $this->messageId = $messageId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        parent::handle();
    }

    protected function getIdentifier()
    {
        return "messageId[$this->messageId]";
    }

    protected function getNotification()
    {
        return DatabaseNotification::where('mail_smtp_id', '=', $this->messageId)->first();
    }
}
