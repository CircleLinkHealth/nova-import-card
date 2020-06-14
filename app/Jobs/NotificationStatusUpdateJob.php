<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

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
}
