<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class AutoApproveValidCarePlansAs extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'QA Approve valid CarePlans pending approval as the given User';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'careplans:approve-as {userId} {--dry}';
    /**
     * @var Collection|null
     */
    private $logs;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /** @var User $user */
        $user = User::ofType('administrator')->findOrFail($this->argument('userId'));

        if ( ! $user->canQAApproveCarePlans()) {
            $this->error($user->id.' is not authorized to approve CarePlans');
        }

        if ($this->option('dry')) {
            $this->warn('DRY RUN. CarePlans will not actually be approved.');
        }

        $user->patientsPendingCLHApproval()->chunkById(50, function ($patients) {
            $patients->each(function (User $patient) {
                $this->warn('processing user:'.$patient->id);
                $needsQA = $patient->carePlan->validator()->fails();

                $this->log([
                    'link'            => route('patient.careplan.print', [$patient->id]),
                    'patient_user_id' => $patient->id,
                    'needs_qa'        => $needsQA,
                ]);

                if ( ! $needsQA) {
                    $patient->carePlan->status = CarePlan::QA_APPROVED;
                    $patient->carePlan->qa_approver_id = $this->argument('userId');
                    $patient->carePlan->qa_date = now()->toDateTimeString();

                    if ( ! $this->option('dry')) {
                        $patient->carePlan->save();
                    }
                }
            });
        });

        if ($this->logs instanceof Collection && $this->logs->isNotEmpty()) {
            $this->table(array_keys($this->logs->first()), $this->logs->all());
        }

        $this->line('All done.');
    }

    private function log(array $array)
    {
        if ( ! $this->logs) {
            $this->logs = collect();
        }

        if ($array['needs_qa']) {
            $this->error("user:{$array['patient_user_id']} needs QA.");
        } else {
            $this->line("user:{$array['patient_user_id']} approved.");
        }

        $this->logs->push($array);
    }
}
