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
    const ALLOCATED_TIME_TO_SEND_ONE_FAX = 120;
    
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $pdfService;
    protected $patients;
    protected $location;
    
    /**
     * Create a new job instance.
     *
     * @param $patients
     * @param Location $location
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
        $counter = 1;
        
        foreach ($this->patients as $patient) {
            $this->location->notify(
                (new CarePlanProviderApproved($patient->carePlan, [FaxChannel::class]))
                    ->setFaxOptions(
                        [
                            'batch_collision_avoidance' => true,
                        ]
                    )->delay($this->delayUntil($counter))
            );
            
            $counter++;
        }
    }
    
    private function delayUntil(int $counter)
    {
        return now()->addSeconds($counter * self::ALLOCATED_TIME_TO_SEND_ONE_FAX);
    }
}
