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
use Illuminate\Support\Facades\Cache;

class AddPcmChargeableServices
{
    public function addPcm(EligibilityJob $eligibilityJob): EligibilityJob
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
            
                $pcmProblemId = $this->matchPcmProblem($eligibilityJob, $p);
            
                if ($pcmProblemId) {
                    $pcmProblems[] = $pcmProblemId->id;
                }
            
                if ( ! empty($pcmProblems = array_unique(array_filter($pcmProblems)))) {
                    $this->addPcmCodeAndProblems($data, $pcmProblems);
                    $eligibilityJob->data = $data;
                }
            }
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
