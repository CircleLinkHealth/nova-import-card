<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Notifications\NotificationStrategies\SendsNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSingleNotification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var SendsNotification
     */
    private $service;

    /**
     * Create a new job instance.
     */
    public function __construct(SendsNotification $service)
    {
        $this->service = $service;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->service->send();
    }
}
