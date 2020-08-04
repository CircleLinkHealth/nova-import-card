<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Core\Helpers\StringHelpers;
use CircleLinkHealth\Customer\AppConfig\CarePlanAutoApprover;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\ImportEnrollee;
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
            User::patientsPendingCLHApproval($approver)->whereHas('practices', function ($q) {
                $q->activeBillable()->whereIsDemo(0);
            })->orderByDesc('id')->chunkById(50, function ($patients) use ($approver) {
                $patients->each(function (User $patient) use ($approver) {
                    $this->process($patient, $approver);
                });
            });
        }

        $consentedCnt = $this->consentedEnrollees()->count();

        $this->warn("$consentedCnt consented patients pending importing");

        $this->consentedEnrollees()->orderBy('consented_at')->chunkById(50, function ($patients) use ($approver) {
            $patients->each(function (Enrollee $enrollee) use ($approver) {
                if ( ! $enrollee->user) {
                    $this->searchForExistingUser($enrollee);
                }
                if ( ! $enrollee->user) {
                    ImportEnrollee::import($enrollee);
                }
                if ($enrollee->user && $enrollee->user->carePlan && in_array($enrollee->user->carePlan->status, [CarePlan::PROVIDER_APPROVED, CarePlan::QA_APPROVED, CarePlan::RN_APPROVED])) {
                    $enrollee->status = Enrollee::ENROLLED;
                    $enrollee->save();

                    return;
                }
                if (is_null($enrollee->user)) {
                    $this->log([
                        'link'            => "Enrollee[{$enrollee->id}] - No User found.",
                        'patient_user_id' => "Enrollee[{$enrollee->id}] - No User found.",
                        'needs_qa'        => true,
                    ]);

                    return;
                }
                $needsQA = $this->process($enrollee->user, $approver);
                if ( ! $this->option('dry') && ! $needsQA) {
                    $enrollee->user->patientInfo->ccm_status = Patient::ENROLLED;
                    $enrollee->status = Enrollee::ENROLLED;
                }
                if ($enrollee->isDirty()) {
                    $enrollee->save();
                }
                if ($enrollee->user->patientInfo->isDirty()) {
                    $enrollee->user->patientInfo->save();
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
            $q->activeBillable()->whereIsDemo(0);
        })->with('user.patientInfo');
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
            $val     = $patient->carePlan->validator();
            $needsQA = $val->fails();
        }

        if ($needsQA && $this->option('reimport')) {
            $this->warn('reimporting user:'.$patient->id);
            $this->reimport($patient->id, $approver->id);
            $patient = $patient->fresh('carePlan');
            if ( ! is_null($patient->carePlan)) {
                $val     = $patient->carePlan->validator();
                $needsQA = $val->fails();
            }
        }

        $this->log([
            'link'            => route('patient.careplan.print', [$patient->id]),
            'patient_user_id' => $patient->id,
            'needs_qa'        => $needsQA,
            'errors'          => $val->errors(),
        ]);

        if ( ! $needsQA && in_array($patient->carePlan->status, [CarePlan::DRAFT, '', null, CarePlan::QA_APPROVED])) {
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

    private function searchForExistingUser(Enrollee &$enrollee)
    {
        $user = User::ofType(['participant', 'survey-only'])
            ->with('carePlan')
            ->whereHas('patientInfo', function ($q) use ($enrollee) {
                $q->where('mrn_number', $enrollee->mrn)
                    ->where('birth_date', $enrollee->dob);
            })->first();

        if ($user
            && StringHelpers::areSameStringsIfYouCompareOnlyLetters($user->first_name.$user->last_name, $enrollee->first_name.$enrollee->last_name)
        ) {
            $enrollee->user_id = $user->id;
            $enrollee->setRelation('user', $user);
        }
    }
}
