<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Models\PracticePull\Demographics;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Adapters\CreatesEligibilityJobFromObject;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Jobs\ProcessSinglePatientEligibility;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\PracticePullMedicalRecord;
use Illuminate\Console\Command;

class DispatchPracticePullEligibilityBatch extends Command
{
    use CreatesEligibilityJobFromObject;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an Eligibility Batch from Practice Data Pull.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'practice:eligibility {practiceId} {--count=-1} {--create-only}';
    private $batch;
    private $practice;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $practiceId = $this->argument('practiceId');
        $count      = $this->option('count');

        ini_set('max_execution_time', 900);

        if ($count < 0) {
            $this->query($practiceId)->chunkById(200, function ($patients) {
                $this->processMany($patients);
            });

            $this->line('Chunking Done!');

            return;
        }

        $this->processMany($this->query($practiceId)->limit($count)->get());

        $this->line("$count jobs ran.");
    }

    protected function dispatchEligibilityJob(Demographics $demos, EligibilityBatch $batch)
    {
        $ej = $this->createFromBlueButtonObject((new PracticePullMedicalRecord($demos->mrn, $demos->practice_id))->toObject(), $batch, $this->practice);

        if ( ! $demos->eligibility_job_id) {
            $demos->eligibility_job_id = $ej->id;
            $demos->save();
        }

        if ( ! $this->option('create-only')) {
            ProcessSinglePatientEligibility::dispatch($ej, $batch, $this->practice);
        }

        return $ej;
    }

    private function createFromPracticePull(Demographics $demos, object $toObject, EligibilityBatch $batch)
    {
        $hash = $this->practice->name.$demos->first_name.$demos->last_name.$demos->mrn.$demos->city.$demos->state.$demos->zip;

        return EligibilityJob::firstOrCreate(
            [
                'batch_id' => $batch->id,
                'hash'     => $hash,
            ],
            [
                'data' => $toObject,
            ]
        );
    }

    private function getBatch()
    {
        if ( ! $this->batch) {
            $this->batch = EligibilityBatch::runningBatch($this->getPractice());
        }

        return $this->batch;
    }

    private function getPractice()
    {
        if ( ! $this->practice) {
            $this->practice = Practice::findOrFail($this->argument('practiceId'));
        }

        return $this->practice;
    }

    private function processMany(iterable $patients)
    {
        foreach ($patients as $demos) {
            $this->warn(Demographics::class.':'.$demos->id);
            $this->dispatchEligibilityJob($demos, $this->getBatch());
            $this->line(Demographics::class.':'.$demos->id);
        }
    }

    private function query(int $practiceId)
    {
        return Demographics::where('practice_id', $practiceId)->whereNull('eligibility_job_id');
    }
}
