<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Jobs;

use CircleLinkHealth\Core\Entities\DatabaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotificationStatusUpdateJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var string
     */
    protected $channel;

    /**
     * @var string
     */
    protected $notificationId;

    /**
     * @var string
     */
    protected $props;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $notificationId, string $channel, array $props)
    {
        $this->channel        = $channel;
        $this->props          = $props;
        $this->notificationId = $notificationId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $notification = $this->getNotification();
        if ( ! $notification) {
            $identifier = $this->getIdentifier();
            Log::warning("could not find notification [$identifier]");

            return;
        }

        $data = $notification->data;
        if (empty($data)) {
            $data = [];
        }
        if ( ! isset($data['status'])) {
            $data['status'] = [];
        }
        if ( ! isset($data['status'][$this->channel])) {
            $data['status'][$this->channel] = [];
        }

        if ($this->isOutOfDateUpdate($data)) {
            return;
        }

        foreach ($this->props as $key => $value) {
            $data['status'][$this->channel][$key] = $value;
        }

        $notification->data = $data;
        $notification->save();
    }

    protected function getIdentifier()
    {
        return $this->notificationId;
    }

    protected function getNotification()
    {
        return DatabaseNotification::find($this->notificationId);
    }

    /**
     * In the case of mail channel:
     * 1. {@link MailChannel} raises the {@link MessageSent} event first
     * 2. Then {@link NotificationSender} raises the {@link NotificationSent} event.
     *
     * {@link NotificationSent} by default sets the status to 'pending'
     * (i.e. pending status update from integration (twilio, sendgrid)
     * So, by the time {@link NotificationSent} is handled, we already have a status 'sent',
     * therefore we skip processing it.
     */
    private function isOutOfDateUpdate(array $notificationData): bool
    {
        if ( ! isset($notificationData['status'][$this->channel]['value'])) {
            return false;
        }

        $statusUpdate = $this->props['value'] ?? null;

        return 'pending' === $statusUpdate;
    }
}
