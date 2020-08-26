<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\AutoCarePlanQAApproval;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ApproveIfValid implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    public Collection $logs;

    public User $patient;

    private User $approver;

    public function __construct(User $patient, User $approver)
    {
        $this->patient  = $patient;
        $this->approver = $approver;
    }

    /**
     * Execute the job.
     *
     * @param \CircleLinkHealth\Eligibility\ProcessEligibilityService $importService
     */
    public function handle()
    {
        $this->logs = collect();

        if ( ! $this->approveIfValid($this->patient, $this->approver)) {
            $this->fail(new \Exception('Patient failed auto QA-approve validation.'));
        }
    }

    /**
     * Processes auto QA approval for patient. Returns true if the CarePlan needs QA.
     *
     * @throws \Exception
     */
    private function approveIfValid(User $patient, User $approver): bool
    {
        if (is_null($patient->carePlan)) {
            $this->logs->push('patient has no CarePlan');

            return false;
        }

        $val = $patient->carePlan->validator();

        $this->logs->push(['data' => $val->getData()]);

        $needsQA = $val->fails();

        if ($needsQA) {
            $this->logs->push(['errors' => $val->errors()->all()]);
        }

        $this->logs->push("needsQA[$needsQA]");

        if ($this->shouldApprove($needsQA, $patient)) {
            $patient->carePlan->status         = CarePlan::QA_APPROVED;
            $patient->carePlan->qa_approver_id = $approver->id;
            $patient->carePlan->qa_date        = now()->toDateTimeString();

            $this->logs->push('approving');

            $patient->carePlan->save();

            $this->logs->push('approved');
        }

        return (bool) $needsQA;
    }

    private function shouldApprove(bool $needsQA, User $patient): bool
    {
        if ($needsQA) {
            $this->logs->push('not approving because CarePlan needs QA');

            return false;
        }

        if (in_array($patient->carePlan->status, [CarePlan::DRAFT, '', null, CarePlan::QA_APPROVED])) {
            $this->logs->push('not approving because CarePlan has status '.$patient->carePlan->status);

            return true;
        }

        return false;
    }
}
