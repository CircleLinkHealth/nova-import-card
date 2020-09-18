<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console;

use Carbon\Carbon;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Make65PlusPatientsEligible extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark all patients over 65 years old as eligible.';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'batch:make-seniors-eligible';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $batch = EligibilityBatch::findOrFail($this->argument('batchId'));

        $cutoffDate = Carbon::parse('65 years ago')->startOfDay();

        $recordsAdded = 0;

        EligibilityJob::whereBatchId($this->argument('batchId'))->doesntHave('enrollee')->chunkById(50, function (Collection $jobs) use ($batch, $cutoffDate, &$recordsAdded) {
            $jobs->each(function (EligibilityJob $job) use ($batch, $cutoffDate, &$recordsAdded) {
                $dob = $job->data['dob'] ?? null;
                if ($dob && Carbon::parse($dob)->isBefore($cutoffDate)) {
                    $this->warn("Creating enrollee for $job->id");
                    $enrollee = Enrollee::create([
                        'batch_id'           => $job->batch_id,
                        'eligibility_job_id' => $job->id,

                        'practice_id' => $batch->practice_id,

                        'mrn' => $job->data['mrn_number'],
                        'dob' => $job->data['dob'],

                        'first_name' => $job->data['first_name'],
                        'last_name'  => $job->data['last_name'],
                        'address'    => $job->data['street'],
                        'address_2'  => $job->data['street2'],
                        'city'       => $job->data['city'],
                        'state'      => $job->data['state'],
                        'zip'        => $job->data['zip'],

                        'lang' => $job->data['language'],

                        'primary_phone' => $job->data['primary_phone'],
                        'cell_phone'    => $job->data['cell_phone'],
                        'home_phone'    => $job->data['home_phone'],
                        'other_phone'   => $job->data['work_phone'] ?? null,

                        'primary_insurance'   => $job->data['primary_insurance'],
                        'secondary_insurance' => $job->data['secondary_insurance'],
                        'tertiary_insurance'  => $job->data['tertiary_insurance'],

                        'email'                   => $job->data['email'],
                        'last_encounter'          => $job->data['last_encounter'],
                        'referring_provider_name' => $job->data['referring_provider_name'],
                        'problems'                => json_encode($job->data['problems']),
                    ]);

                    ++$recordsAdded;

                    $this->line("job:$job->id enrollee:$enrollee->id");
                }
            });
        });

        $options                                  = $batch->options;
        $options['makeAllPatientsOver65Eligible'] = 'Ran last on '.presentDate(now())." and yielded $recordsAdded additional eligible patients.";
        $batch->options                           = $options;
        $batch->save();

        $this->line("Command finished. $recordsAdded patients over 65 were marked as eligible.");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['batchId', InputArgument::REQUIRED, 'The ID of the Eligibility Batch.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
