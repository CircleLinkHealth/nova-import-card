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
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\CcdaMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\CommonwealthMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\PracticePullMedicalRecord;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Support\Str;

class MedicalRecordFactory
{
    /**
     * @var Enrollee
     */
    private $enrollee;

    public static function create(User $user, Ccda $ccda)
    {
        $static     = new static();
        $methodName = 'create'.ucfirst(Str::camel($user->primaryPractice->name)).'MedicalRecord';

        if (method_exists($static, $methodName)) {
            return $static->{$methodName}($user, $ccda);
        }

        return $static->createDefaultMedicalRecord($user, $ccda);
    }

    /**
     * @throws \Exception
     *
     * @return CommonwealthMedicalRecord
     */
    public function createCommonwealthPainAssociatesPllcMedicalRecord(User $user, Ccda $ccda)
    {
        return new CommonwealthMedicalRecord(
            app(PcmChargeableServices::class)->decorate(
                app(MedicalHistoryFromAthena::class)->decorate(
                    app(InsuranceFromAthena::class)->decorate(
                        app(DemographicsFromAthena::class)->decorate(
                            app(CcdaFromAthena::class)->setCcda($ccda)->setPatientUser($user)->decorate(
                                $this->getEligibilityJobWithTargetPatient($user)->eligibilityJob
                            )
                        )
                    )
                )
            )->data,
            new CcdaMedicalRecord($ccda->bluebuttonJson())
        );
    }

    public function createDefaultMedicalRecord(User $user, Ccda $ccda)
    {
        return new CcdaMedicalRecord(json_decode($ccda->json));
    }

    public function createToledoClinicMedicalRecord(User $user, Ccda $ccda)
    {
        return new PracticePullMedicalRecord($ccda->patient_mrn, $ccda->practice_id);
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
                'eligibilityJob.targetPatient'
            )->has('eligibilityJob')->orderByRaw('care_ambassador_user_id, preferred_days, preferred_window, id desc')->firstOrFail();

            if (is_null($this->enrollee->user_id)) {
                $this->enrollee->user_id = $user->id;
                $this->enrollee->save();
            }
        }

        return $this->enrollee;
    }
}
