<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Contracts\SendsNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAWVDocument implements ShouldQueue
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
     *
     * @param SendsNotification $service
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
