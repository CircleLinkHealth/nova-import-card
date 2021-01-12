<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Notifications\CarePlansGeneratedNotification;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Services\CarePlanGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GeneratePatientsCarePlans implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    private Carbon $dateRequested;
    private bool $letter;

    private int $requesterId;
    private array $userIds;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $requesterId, Carbon $dateRequested, array $userIds, bool $letter)
    {
        $this->requesterId   = $requesterId;
        $this->dateRequested = $dateRequested;
        $this->userIds       = $userIds;
        $this->letter        = $letter;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CarePlanGeneratorService $service)
    {
        Log::debug('Ready to run GeneratePatientsCarePlans');

        /** @var Media $media */
        $media = $service->pdfForUsers($this->requesterId, $this->userIds, $this->letter);
        Log::debug("Pdf for users generated. See media[$media->id]");

        User::find($this->requesterId)->notify(new CarePlansGeneratedNotification(optional($media)->id, $this->dateRequested));
    }
}
