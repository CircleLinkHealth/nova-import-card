<?php

namespace App\CLH\CCD\Importer\Parsers;

use App\CLH\CCD\Importer\CPMProblem;
use App\CLH\Contracts\CCD\Parser;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use Illuminate\Support\Facades\Log;

class ProblemsParser extends BaseParser implements Parser
{
    public function parse()
    {
        $ccdProblems = $this->ccd->problems;
        $importIfEndDateIsNull = $this->importIfEndDateIsNullAndStartDateExists();
        $problemsList = '';

        foreach ($ccdProblems as $ccdProblem)
        {
            /**
             * Problems can only be active or chronic, unless this is a CCD we can $importIfEndDateIsNull
             */
            if (! $importIfEndDateIsNull) {
                if (! in_array(strtolower($ccdProblem->status), ['active', 'chronic'])) continue;
            }

            /**
             * Special case CCDs can only be imported if there is a start date, but no end date.
             * See importIfEndDateIsNullAndStartDateExists()
             */
            if ($importIfEndDateIsNull
                && (empty($ccdProblem->date_range->start) || ! empty($ccdProblem->date_range->end))) continue;

            /**
             * Check if the information is in the Translation Section of BB
             */
            $problemCodes = $ccdProblem;

            if (empty($problemCodes->code) || empty($problemCodes->code_system_name)) {
                $problemCodes = $ccdProblem->translation;
            }

            /**
             * If all fields are empty, then skip this problem to avoid having ,,; on the problems list
             */
            if (empty($problemCodes->name)
                && empty($problemCodes->code_system_name)
                && empty($problemCodes->code)) continue;

            $problemsList .= ucwords(strtolower($problemCodes->name)) . ', '
                . strtoupper($problemCodes->code_system_name) . ', '
                . $problemCodes->code . ";\n\n";
        }

        return $problemsList;
    }

    public function save($problemsList)
    {
        if (empty($problemsList)) return;

        if (empty($this->blogId) or empty($this->userId)) throw new \Exception('UserID and BlogID are required.');

        $pcp = CPRulesPCP::whereProvId($this->blogId)->whereSectionText('Diagnosis / Problems to Monitor')->first();
        if (empty($pcp)) {
            Log::error(__METHOD__ . ' ' . __LINE__ . ' for userID ' . $this->userId . ', blogId ' . $this->blogId . ' has failed.');
            return;
        }
        $pcpId = $pcp->pcp_id;

        $rulesItem = CPRulesItem::wherePcpId($pcpId)->whereItemsText('Other Conditions')->first();
        if (empty($rulesItem)) {
            Log::error( __METHOD__ . ' ' . __LINE__ . ' for userID ' . $this->userId . ', blogId ' . $this->blogId . ' has failed.');
            return;
        }
        $parentItemId = $rulesItem->items_id;

        $details = CPRulesItem::wherePcpId($pcpId)->whereItemsParent($parentItemId)->whereItemsText('Details')->first();
        if (empty($details)) {
            Log::error( __METHOD__ . ' ' . __LINE__ . ' for userID ' . $this->userId . ', blogId ' . $this->blogId . ' has failed.');
            return;
        }
        $itemId = $details->items_id;

        //Set UI Item to Active
        CPRulesUCP::updateOrCreate([
            'items_id' => $parentItemId,
            'user_id' => $this->userId,
            'meta_key' => 'status',
        ], [
            'meta_value' => 'Active',
        ]);

        //Value
        CPRulesUCP::updateOrCreate([
            'items_id' => $itemId,
            'user_id' => $this->userId,
            'meta_key' => 'value',
        ], [
            'meta_value' => $problemsList,
        ]);
    }

    public function activateCPProblems()
    {
        $cpmProblems = CPMProblem::all();
        $ccdProblems = $this->ccd->problems;
        $importIfEndDateIsNull = $this->importIfEndDateIsNullAndStartDateExists();

        foreach ($ccdProblems as $ccdProblem)
        {
            /**
             * Problems can only be active or chronic, unless this is a CCD we can $importIfEndDateIsNull
             */
            if (! $importIfEndDateIsNull) {
                if (! in_array(strtolower($ccdProblem->status), ['active', 'chronic'])) continue;
            }

            /**
             * Check if the information is in the Translation Section of BB
             */
            $problemCodes = $ccdProblem;

            if (empty($problemCodes->code) || empty($problemCodes->code_system_name)) {
                $problemCodes = $ccdProblem->translation;
            }

            if ($importIfEndDateIsNull
                && (empty($ccdProblem->date_range->start) || ! empty($ccdProblem->date_range->end))) continue;

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
                        $this->activateCPProblem($cpmProblem->name);
                        break;
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
                    /**
                     * Since we are doing string comparison, I10 != I10.0
                     * This is is to prevent this
                     */
                    if (! strpos($problemCodes->code, '.')) {
                        $problemCodes->code .= '.0';
                    }

                    if ((string) $problemCodes->code >= (string) $cpmProblem->icd10from
                        && (string) $problemCodes->code <= (string) $cpmProblem->icd10to)
                    {
                        $this->activateCPProblem($cpmProblem->name);
                        break;
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

                    if (strpos($problemCodes->name, $keyword))
                    {
                        $this->activateCPProblem($cpmProblem->name);
                        continue;
                    }
                }
            }
        }
    }

    private function activateCPProblem($cpmProblemName)
    {
        if (empty($this->blogId) or empty($this->userId)) throw new \Exception('UserID and BlogID are required.');

        $pcp = CPRulesPCP::whereProvId($this->blogId)->whereSectionText('Diagnosis / Problems to Monitor')->first();
        if (empty($pcp)) {
            Log::error(__METHOD__ . ' ' . __LINE__ . ' for userID ' . $this->userId . ', blogId ' . $this->blogId . ' has failed.');
            return;
        }
        $pcpId = $pcp->pcp_id;

        $rulesItem = CPRulesItem::wherePcpId($pcpId)->whereItemsText($cpmProblemName)->first();
        if (empty($rulesItem)) {
            Log::error( __METHOD__ . ' ' . __LINE__ . ' for userID ' . $this->userId . ', blogId ' . $this->blogId . ' has failed.');
            return;
        }
        $parentItemId = $rulesItem->items_id;

        //Set UI Item to Active
        CPRulesUCP::updateOrCreate([
            'items_id' => $parentItemId,
            'user_id' => $this->userId,
            'meta_key' => 'status',
        ], [
            'meta_value' => 'Active',
        ]);
    }
}