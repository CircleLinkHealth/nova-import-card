<?php

namespace App\Jobs;

use App\Notifications\CarePlanProviderApproved;
use App\Notifications\Channels\FaxChannel;
use CircleLinkHealth\Core\PdfService;
use CircleLinkHealth\Customer\Entities\Location;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FaxPatientCarePlansToLocation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pdfService;
    protected $patients;
    protected $location;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($patients, Location $location)
    {
        $this->patients = $patients;
        $this->location = $location;
        $this->pdfService = app(PdfService::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->patients as $patient){
            $this->location->notify(new CarePlanProviderApproved($patient->carePlan, [FaxChannel::class]));
        }
    }
}
