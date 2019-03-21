<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateProviderReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Patient to attempt to create provider report for.
     *
     * @var array
     */
    protected $patientId;

    /**
     * Date to specify for which survey instances to generate Provider Report for.
     *
     * @var Carbon
     */
    protected $date;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($patientId, $date)
    {
        $this->patientId = $patientId;

        $this->date = Carbon::parse($date);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //check if it has a report already with those instances
    }
}
