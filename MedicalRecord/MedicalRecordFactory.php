<?php

namespace CircleLinkHealth\Eligibility\MedicalRecord;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Decorators\DemographicsFromAthena;
use CircleLinkHealth\Eligibility\Decorators\InsuranceFromAthena;
use CircleLinkHealth\Eligibility\Decorators\MedicalHistoryFromAthena;
use CircleLinkHealth\Eligibility\Decorators\PcmChargeableServices;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\CcdaMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\CommonwealthMedicalRecord;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class MedicalRecordFactory
{
    /**
     * @var Enrollee
     */
    private $enrollee;
    
    /**
     * @param User $user
     * @param Ccda $ccda
     *
     * @return CommonwealthMedicalRecord
     * @throws \Exception
     */
    public function createCommonwealthPainAssociatesPllcMedicalRecord(User $user, Ccda $ccda)
    {
        return new CommonwealthMedicalRecord(
            app(PcmChargeableServices::class)->decorate(
                app(MedicalHistoryFromAthena::class)->decorate(
                    app(InsuranceFromAthena::class)->decorate(
                        app(DemographicsFromAthena::class)->decorate(
                            $this->getEnrollee($user)->eligibilityJob
                        )
                    )
                )
            )->data,
            new CcdaMedicalRecord($ccda->bluebuttonJson())
        );
    }
    
    private function getEnrollee(User $user): Enrollee
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
                'eligibilityJob'
            )->has('eligibilityJob')->orderByDesc('id')->firstOrFail();;
        }
        
        return $this->enrollee;
    }
    
    public function createDefaultMedicalRecord(User $user, Ccda $ccda)
    {
        return new CcdaMedicalRecord(json_decode($ccda->json));
    }
    
    public static function create(User $user, Ccda $ccda)
    {
        $static = new static();
        $methodName = 'create'.ucfirst(camel_case($user->primaryPractice->name)).'MedicalRecord';
        
        if (method_exists($static, $methodName)) {
            return $static->{$methodName}($user, $ccda);
        }
        
        return $static->createDefaultMedicalRecord($user, $ccda);
    }
}