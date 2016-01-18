<?php

namespace App\CLH\CCD\Importer\Parsers;

use App\CLH\CCD\Importer\CPMProblem;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use Illuminate\Support\Facades\Log;

class ProblemsParser extends BaseParser
{
    public function parse()
    {
        $cpmProblems = CPMProblem::all();
        $ccdProblems = $this->ccd->problems;

        foreach ($ccdProblems as $ccdProblem)
        {
            /**
             * Problems can only be active or chronic
             */
            if (! in_array(strtolower($ccdProblem->status), ['active', 'chronic'])) return;

            /**
             * Check if the information is in the Translation Section of BB
             */
            $problemCodes = $ccdProblem;

            if (empty($problemCodes->code)) {
                $problemCodes = $ccdProblem->translation;
            }

            /*
             * ICD-9 Check
             */
            if (in_array($problemCodes->code_system_name, ['ICD-9', 'ICD9']) || $problemCodes->code_system == '2.16.840.1.113883.6.103')
            {
                foreach ($cpmProblems as $cpmProblem)
                {
                    if ($problemCodes->code >= $cpmProblem->icd9from
                        && $problemCodes->code <= $cpmProblem->icd9to)
                    {
                        $this->createOrUpdateProblems($this->userId, $this->blogId, $cpmProblem->name);
                        continue;
                    }
                }

                continue;
            }

            /*
             * ICD-10 Check
             */
            if (in_array($problemCodes->code_system_name, ['ICD-10', 'ICD10']) || $problemCodes->code_system == '2.16.840.1.113883.6.3')
            {
                foreach ($cpmProblems as $cpmProblem)
                {
                    if ((string) $problemCodes->code >= (string) $cpmProblem->icd10from
                        && (string) $problemCodes->code <= (string) $cpmProblem->icd10to)
                    {
                        $this->createOrUpdateProblems($this->userId, $this->blogId, $cpmProblem->name);
                        continue;
                    }
                }

                continue;
            }

            /*
             * Try to match keywords
             */
            foreach ($cpmProblems as $cpmProblem)
            {
                $keywords = explode(',', $cpmProblem->contains);

                foreach ($keywords as $keyword)
                {
                    if (empty($keyword)) continue;

                    if (strpos($cpmProblem->name, $keyword))
                    {
                        $this->createOrUpdateProblems($this->userId, $this->blogId, $cpmProblem->name);
                        continue;
                    }
                }
            }
        }
    }

    private function createOrUpdateProblems($userId, $blogId, $cpmProblem)
    {
        if (empty($blogId) or empty($userId)) throw new \Exception('UserID and BlogID are required.');

        $pcp = CPRulesPCP::whereProvId($blogId)->whereSectionText('Diagnosis / Problems to Monitor')->first();
        if (empty($pcp)) {
            Log::error(__METHOD__ . ' ' . __LINE__ . ' for userID ' . $userId . ', blogId ' . $blogId . ' has failed.');
            return;
        }
        $pcpId = $pcp->pcp_id;

        $rulesItem = CPRulesItem::wherePcpId($pcpId)->whereItemsText($cpmProblem)->first();
        if (empty($rulesItem)) {
            Log::error( __METHOD__ . ' ' . __LINE__ . ' for userID ' . $userId . ', blogId ' . $blogId . ' has failed.');
            return;
        }
        $parentItemId = $rulesItem->items_id;

//        $details = CPRulesItem::wherePcpId($pcpId)->whereItemsParent($parentItemId)->whereItemsText('Details')->first();
//        if (empty($details)) {
//            Log::error( __METHOD__ . ' ' . __LINE__ . ' for userID ' . $userId . ', blogId ' . $blogId . ' has failed.');
//            return;
//        }
//        $itemId = $details->items_id;

        //Set UI Item to Active
        CPRulesUCP::updateOrCreate([
            'items_id' => $parentItemId,
            'user_id' => $userId,
            'meta_key' => 'status',
        ], [
            'meta_value' => 'Active',
        ]);

        //Value
//        CPRulesUCP::updateOrCreate([
//            'items_id' => $itemId,
//            'user_id' => $userId,
//            'meta_key' => 'value',
//        ], [
//            'meta_value' => $medsList,
//        ]);
    }
}