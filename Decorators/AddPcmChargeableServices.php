<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 2/13/20
 * Time: 2:51 PM
 */

namespace CircleLinkHealth\Eligibility\Decorators;


use CircleLinkHealth\Eligibility\EligibilityChecker;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\PcmProblem;
use CircleLinkHealth\Eligibility\Entities\Problem;

class AddPcmChargeableServices
{
    public function decorate(EligibilityJob $eligibilityJob): EligibilityJob
    {
        $data = $eligibilityJob->data;
        $pcmProblems = [];
    
        $problems = EligibilityChecker::getProblemsForEligibility($eligibilityJob);
    
        if ($problems) {
            $eligibilityJob->loadMissing('batch');
            
            /** @var Problem $p */
            foreach ($problems as $p) {
                if ( ! is_a($p, Problem::class)) {
                    continue;
                }
            
                $pcmProblemId = PcmProblem::where('practice_id', $eligibilityJob->batch->practice_id)->where(
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
            
                if ($pcmProblemId) {
                    $pcmProblems[] = $pcmProblemId->id;
                }
            
                if ( ! empty($pcmProblems = array_unique(array_filter($pcmProblems)))) {
                    $data['chargeable_services']['G2065']['problems'] = $pcmProblems;
                    $eligibilityJob->data = $data;
                }
            }
        }
        
        return $eligibilityJob;
    }
}