<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\AppConfig\CarePlanAutoApprover;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Console\ReimportPatientMedicalRecord;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
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
    protected $signature = 'careplans:approve-as {userId?} {--dry} {--reimport} {--reimport:clear} {--reimport:without-transaction} {--only-consented-enrollees}';
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
        /** @var User $approver */
        $approver = User::ofType('administrator')->findOrFail($this->argument('userId') ?? CarePlanAutoApprover::id());

        if ( ! $approver->canQAApproveCarePlans()) {
            $this->error($approver->id.' is not authorized to approve CarePlans');
        }

        if ($this->option('dry')) {
            $this->warn('DRY RUN. CarePlans will not actually be approved.');
        }

        if ( ! $this->option('only-consented-enrollees')) {
            $approver->patientsPendingCLHApproval()->chunkById(50, function ($patients) use ($approver) {
                $patients->each(function (User $patient) use ($approver) {
                    $this->process($patient, $approver);
                });
            });
        }

        $this->consentedEnrollees()->chunkById(50, function ($patients) use ($approver) {
            $patients->each(function (Enrollee $enrollee) use ($approver) {
                $needsQA = $this->process($enrollee->user, $approver);
                if ( ! $this->option('dry') && ! $needsQA) {
                    $enrollee->status = Enrollee::ENROLLED;
                    $enrollee->save();
                }
            });
        });

        if ($this->logs instanceof Collection && $this->logs->isNotEmpty()) {
            $this->table(array_keys($this->logs->first()), $this->logs->all());
        }

        $this->line('Command done done.');
    }

    private function consentedEnrollees()
    {
        return Enrollee::where('status', Enrollee::CONSENTED)->whereHas('practice', function ($q) {
            $q->activeBillable();
        })->has('user')->with('user');
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

    /**
     * Processes auto QA approval for patient. Returns true if the CarePlan needs QA.
     *
     * @throws \Exception
     */
    private function process(User $patient, User $approver): bool
    {
        $this->warn('processing user:'.$patient->id);
        $needsQA = true;

        if ( ! is_null($patient->carePlan)) {
            $needsQA = $patient->carePlan->validator()->fails();
        }

        if ($needsQA && $this->option('reimport')) {
            $this->warn('reimporting user:'.$patient->id);
            $this->reimport($patient->id, $approver->id);
            $patient = $patient->fresh('carePlan');
            if ( ! is_null($patient->carePlan)) {
                $needsQA = $patient->carePlan->validator()->fails();
            }
        }

        $this->log([
            'link'            => route('patient.careplan.print', [$patient->id]),
            'patient_user_id' => $patient->id,
            'needs_qa'        => $needsQA,
        ]);

        if ( ! $needsQA) {
            $patient->carePlan->status         = CarePlan::QA_APPROVED;
            $patient->carePlan->qa_approver_id = $approver->id;
            $patient->carePlan->qa_date        = now()->toDateTimeString();

            if ( ! $this->option('dry')) {
                $patient->carePlan->save();
            }
        }

        return (bool) $needsQA;
    }

    private function reimport(int $patientUserId, int $approverUserId)
    {
        $args = [];
        if ($this->option('reimport:clear')) {
            $args['--clear'] = true;
        }
        if ($this->option('reimport:without-transaction')) {
            $args['--without-transaction'] = true;
        }
        ReimportPatientMedicalRecord::for($patientUserId, $approverUserId, 'call', $args);
    }
}
