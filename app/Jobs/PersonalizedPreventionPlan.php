<?php

namespace App\Jobs;

use App\Services\GeneratePersonalizedPreventionPlanService;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PersonalizedPreventionPlan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $patient;

    protected $service;

    /**
     * Create a new job instance.
     *
     * @param $patientId
     */
    public function __construct($patientId)
    {
        $this->patient = User::with([
            'patientInfo',
            'billingProvider'
        ])->findOrFail($patientId);

        $this->service = new GeneratePersonalizedPreventionPlanService($this->patient);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->service->generateData($this->patient);
    }
}
