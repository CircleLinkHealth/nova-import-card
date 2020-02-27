<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 2/13/20
 * Time: 2:51 PM
 */

namespace CircleLinkHealth\Eligibility\Decorators;


use CircleLinkHealth\Eligibility\Contracts\MedicalRecordDecorator;
use CircleLinkHealth\Eligibility\EligibilityChecker;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\PcmProblem;
use CircleLinkHealth\Eligibility\Entities\Problem;
use Illuminate\Support\Facades\Cache;

class PcmChargeableServices implements MedicalRecordDecorator
{
    public function decorate(EligibilityJob $eligibilityJob): EligibilityJob
    {
        $data = $eligibilityJob->data;
        $pcmProblems = [];
    
        $eligibilityJob->loadMissing('batch');
        
        /** @var Problem $p */
        foreach (EligibilityChecker::getProblemsForEligibility($eligibilityJob) as $p) {
            if ( ! is_a($p, Problem::class)) {
                continue;
            }
        
            $pcmProblemId = $this->matchPcmProblem($eligibilityJob, $p);
        
            if ($pcmProblemId) {
                $pcmProblems[] = $pcmProblemId->id;
            }
        }
        
        if (empty($pcmProblems)) {
            return $eligibilityJob;
        }
    
        $this->addPcmCodeAndProblems($data, $pcmProblems);
        
        if ($eligibilityJob->isDirty()) {
            $eligibilityJob->save();
        }
        
        return $eligibilityJob;
    }
    
    private function matchPcmProblem(EligibilityJob $eligibilityJob, Problem $p)
    {
        return Cache::remember(sha1($eligibilityJob->batch->practice_id.$p->getCode().$p->getName()),2, function () use ($eligibilityJob, $p) {
            return PcmProblem::where('practice_id', $eligibilityJob->batch->practice_id)->where(
                function ($q) use ($p) {
                    $q->where(
                        'code',
                        $p->getCode()
                    )->orWhere(
                        'description',
                        $p->getName()
                    );
                }
            )->first();
        });
    }
    
    private function addPcmCodeAndProblems(array &$data, array $pcmProblems)
    {
        $data['chargeable_services_codes_and_problems']['G2065'] = $pcmProblems;
    }
}
