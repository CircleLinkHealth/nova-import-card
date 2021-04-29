<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotificationStatusUpdateJob implements ShouldQueue, ShouldBeEncrypted
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

        if ($this->isOutOfDateUpdate($data['status'][$this->channel]['value'] ?? null, $this->props['value'], $notification->updated_at)) {
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
     *
     * {@link NotificationSent event is always raised, even if NotificationFailed was raised.
     * So we have to check if the notification failed just now.
     */
    private function isOutOfDateUpdate(?string $currentStatus, ?string $newStatus, ?Carbon $lastUpdate): bool
    {
        if ( ! $currentStatus) {
            return false;
        }

        if ('failed' === $currentStatus && ! is_null($lastUpdate) && $lastUpdate->diffInMilliseconds(now()) < 1000) {
            return true;
        }

        if ('mail' === $this->channel) {
            if ('sending' === $currentStatus && 'pending' === $newStatus) {
                return true;
            }

            if ('sent' === $currentStatus && ('pending' === $newStatus || 'sending' === $newStatus)) {
                return true;
            }
        }

        return 'pending' === $newStatus;
    }
}
