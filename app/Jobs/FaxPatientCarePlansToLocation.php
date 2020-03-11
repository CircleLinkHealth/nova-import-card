<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Notifications\CarePlanProviderApproved;
use App\Notifications\Channels\FaxChannel;
use CircleLinkHealth\Core\PdfService;
use CircleLinkHealth\Customer\Entities\Location;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FaxPatientCarePlansToLocation implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    protected $location;
    protected $patients;

    protected $pdfService;

    /**
     * Create a new job instance.
     *
     * @param mixed $patients
     *
     * @return void
     */
    public function __construct($patients, Location $location)
    {
        $this->patients   = $patients;
        $this->location   = $location;
        $this->pdfService = app(PdfService::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->patients as $patient) {
            $this->location->notify(
                (new CarePlanProviderApproved($patient->carePlan, [FaxChannel::class]))
                    ->setFaxOptions(
                        [
                            'batch_collision_avoidance' => true,
                            'batch_delay'               => 60,
                        ]
                    )
            );
        }
    }
}
