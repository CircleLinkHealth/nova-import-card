<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecord;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Decorators\CcdaFromAthena;
use CircleLinkHealth\Eligibility\Decorators\DemographicsFromAthena;
use CircleLinkHealth\Eligibility\Decorators\InsuranceFromAthena;
use CircleLinkHealth\Eligibility\Decorators\MedicalHistoryFromAthena;
use CircleLinkHealth\Eligibility\Decorators\PcmChargeableServices;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\CcdaMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\CommonwealthMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\PracticePullMedicalRecord;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Support\Str;

class MedicalRecordFactory
{
    /**
     * @var Enrollee
     */
    private $enrollee;

    public static function create(User $user, ?Ccda $ccda)
    {
        $static     = new static();
        $methodName = 'create'.ucfirst(Str::camel($user->primaryPractice->name)).'MedicalRecord';

        if (method_exists($static, $methodName)) {
            return $static->{$methodName}($user, $ccda);
        }

        if (is_null($ccda)) {
            return null;
        }

        return $static->createDefaultMedicalRecord($user, $ccda);
    }

    public function createCameronMemorialMedicalRecord(User $user, ?Ccda $ccda)
    {
        return new PracticePullMedicalRecord(optional($ccda)->patient_mrn ?? $user->getMRN(), optional($ccda)->practice_id ?? $user->program_id);
    }

    /**
     * @throws \Exception
     *
     * @return CommonwealthMedicalRecord
     */
    public function createCommonwealthPainAssociatesPllcMedicalRecord(User $user, ?Ccda $ccda)
    {
        return new CommonwealthMedicalRecord(
            app(PcmChargeableServices::class)->decorate(
                app(MedicalHistoryFromAthena::class)->decorate(
                    app(InsuranceFromAthena::class)->decorate(
                        app(DemographicsFromAthena::class)->decorate(
                            $ej = app(CcdaFromAthena::class)->setCcda($ccda)->setPatientUser($user)->decorate(
                                $this->getEligibilityJobWithTargetPatient($user)->eligibilityJob
                            )
                        )
                    )
                )
            )->data,
            new CcdaMedicalRecord(optional($ccda)->bluebuttonJson() ?? $ej->targetPatient->ccda->bluebuttonJson())
        );
    }

    public function createDefaultMedicalRecord(User $user, Ccda $ccda)
    {
        return new CcdaMedicalRecord($ccda->bluebuttonJson(true));
    }

    public function createToledoClinicMedicalRecord(User $user, ?Ccda $ccda)
    {
        return new PracticePullMedicalRecord(optional($ccda)->patient_mrn ?? $user->getMRN(), optional($ccda)->practice_id ?? $user->program_id);
    }

    public function createWoodlandsInternistsPaMedicalRecord(User $user, ?Ccda $ccda)
    {
        return new PracticePullMedicalRecord(optional($ccda)->patient_mrn ?? $user->getMRN(), optional($ccda)->practice_id ?? $user->program_id);
    }

    private function getEligibilityJobWithTargetPatient(User $user)
    {
        if ( ! $this->enrollee) {
            $this->enrollee = Enrollee::where(
                [
                    ['mrn', '=', $user->getMRN()],
                    ['practice_id', '=', $user->program_id],
                    ['first_name', '=', $user->first_name],
                    ['last_name', '=', $user->last_name],
                ]
            )->where(
                function ($q) use ($user) {
                    $q->whereNull('user_id')->orWhere('user_id', $user->id);
                }
            )->with(
                'eligibilityJob.targetPatient.ccda'
            )->has('eligibilityJob')->orderByRaw('care_ambassador_user_id, preferred_days, preferred_window, id desc')->firstOrFail();

            if (is_null($this->enrollee->user_id)) {
                $this->enrollee->user_id = $user->id;
                $this->enrollee->save();
            }
        }

        return $this->enrollee;
    }
}