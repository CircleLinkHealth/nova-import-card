<?php

namespace App\CLH\CCD\Importer\Validators;

use App\CLH\CCD\Importer\CPMProblem;
use App\CLH\CCD\Importer\Validators\BaseValidator;
use App\CLH\CCD\Importer\SnomedToICD10Map;
use App\CLH\Contracts\CCD\Parser;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProblemsValidator extends BaseValidator implements Parser
{
    public function parse()
    {
        $ccdProblems = $this->ccd->problems;
        $importIfEndDateIsNull = $this->importIfEndDateIsNullAndStartDateExists();
        $problemsList = '';

        foreach ( $ccdProblems as $ccdProblem ) {
            /**
             * Problems can only be active or chronic, unless this is a CCD we can $importIfEndDateIsNull
             */
            if ( !$importIfEndDateIsNull ) {
                if ( !in_array( strtolower( $ccdProblem->status ), ['active', 'chronic'] ) ) continue;
            }

            /**
             * Check if end date has passed, if it's not a $importIfEndDateIsNull CCD
             */
            $endDate = '';

            if ( !empty($ccdProblem->date_range->end) ) {
                $endDate = Carbon::createFromTimestamp( strtotime( $ccdProblem->date_range->end ) );
            }

            if ( (!empty($endDate) && $endDate->isPast()) && !$importIfEndDateIsNull ) continue;

            /**
             * Special case CCDs can only be imported if there is a start date, but no end date.
             * See importIfEndDateIsNullAndStartDateExists()
             */
            if ( $importIfEndDateIsNull
                && (empty($ccdProblem->date_range->start) || !empty($ccdProblem->date_range->end))
            ) continue;

            /**
             * Check if the information is in the Translation Section of BB
             */
            $problemCodes = $this->consolidateProblemInfo( $ccdProblem );

            /**
             * If all fields are empty, then skip this problem to avoid having ,,; on the problems list
             */
            if ( empty($problemCodes->name)
                && empty($problemCodes->code_system_name)
                && empty($problemCodes->code)
            ) continue;

            $problemsList .= ucwords( strtolower( $problemCodes->name ) ) . ', '
                . strtoupper( $problemCodes->code_system_name ) . ', '
                . $problemCodes->code . ";\n\n";
        }

        return $problemsList;
    }

    public function save($problemsList)
    {
        if ( empty($problemsList) ) return;

        if ( empty($this->blogId) or empty($this->userId) ) throw new \Exception( 'UserID and BlogID are required.' );

        $pcp = CPRulesPCP::whereProvId( $this->blogId )->whereSectionText( 'Diagnosis / Problems to Monitor' )->first();
        if ( empty($pcp) ) {
            Log::error( __METHOD__ . ' ' . __LINE__ . ' for userID ' . $this->userId . ', blogId ' . $this->blogId . ' has failed.' );
            return;
        }
        $pcpId = $pcp->pcp_id;

        $rulesItem = CPRulesItem::wherePcpId( $pcpId )->whereItemsText( 'Other Conditions' )->first();
        if ( empty($rulesItem) ) {
            Log::error( __METHOD__ . ' ' . __LINE__ . ' for userID ' . $this->userId . ', blogId ' . $this->blogId . ' has failed.' );
            return;
        }
        $parentItemId = $rulesItem->items_id;

        $details = CPRulesItem::wherePcpId( $pcpId )->whereItemsParent( $parentItemId )->whereItemsText( 'Details' )->first();
        if ( empty($details) ) {
            Log::error( __METHOD__ . ' ' . __LINE__ . ' for userID ' . $this->userId . ', blogId ' . $this->blogId . ' has failed.' );
            return;
        }
        $itemId = $details->items_id;

        //Set UI Item to Active
        CPRulesUCP::updateOrCreate( [
            'items_id' => $parentItemId,
            'user_id' => $this->userId,
            'meta_key' => 'status',
        ], [
            'meta_value' => 'Active',
        ] );

        //Value
        CPRulesUCP::updateOrCreate( [
            'items_id' => $itemId,
            'user_id' => $this->userId,
            'meta_key' => 'value',
        ], [
            'meta_value' => $problemsList,
        ] );
    }

    public function activateCPProblems()
    {
        $cpmProblems = CPMProblem::all();
        $ccdProblems = $this->ccd->problems;
        $importIfEndDateIsNull = $this->importIfEndDateIsNullAndStartDateExists();

        foreach ( $ccdProblems as $ccdProblem ) {
            /**
             * Problems can only be active or chronic, unless this is a CCD we can $importIfEndDateIsNull
             */
            if ( !$importIfEndDateIsNull ) {
                if ( !in_array( strtolower( $ccdProblem->status ), ['active', 'chronic'] ) ) continue;
            }

            /**
             * Check if the information is in the Translation Section of BB
             */
            $problemCodes = $this->consolidateProblemInfo( $ccdProblem );

            if ( $importIfEndDateIsNull
                && (empty($ccdProblem->date_range->start) || !empty($ccdProblem->date_range->end))
            ) continue;

            /*
             * ICD-9 Check
             */
            if ( in_array( $problemCodes->code_system_name, ['ICD-9', 'ICD9'] ) || $problemCodes->code_system == '2.16.840.1.113883.6.103' ) {
                foreach ( $cpmProblems as $cpmProblem ) {
                    if ( $problemCodes->code >= $cpmProblem->icd9from
                        && $problemCodes->code <= $cpmProblem->icd9to
                    ) {
                        $this->activateCPProblem( $cpmProblem->name );
                        break;
                    }
                }

                continue;
            }

            /*
             * ICD-10 Check
             */
            if ( in_array( $problemCodes->code_system_name, ['ICD-10', 'ICD10'] ) || $problemCodes->code_system == '2.16.840.1.113883.6.3' ) {
                if ( !empty($potentialICD10List) ) {
                    /**
                     * This is the same code as a few lines down.
                     * @todo: refactor soon
                     */
                    foreach ( $potentialICD10List as $icd10 ) {
                        foreach ( $cpmProblems as $cpmProblem ) {
                            /**
                             * Since we are doing string comparison, I10 != I10.0
                             * This is is to prevent this
                             */
                            if ( !strpos( $icd10, '.' ) ) {
                                $icd10 .= '.0';
                            }

                            if ( (string)$icd10 >= (string)$cpmProblem->icd10from
                                && (string)$icd10 <= (string)$cpmProblem->icd10to
                            ) {
                                $this->activateCPProblem( $cpmProblem->name );
                                continue 3;
                            }
                        }
                    }
                }

                /*
                 * SNOMED Check
                 */
                if ( in_array( $problemCodes->code_system_name, ['SNOMED CT'] ) || $problemCodes->code_system == '2.16.840.1.113883.6.96' ) {
                    $potentialICD10List = SnomedToICD10Map::whereSnomedCode( $problemCodes->code )->lists( 'icd_10_code' );
                    $problemCodes->code_system_name = 'ICD-10';
                    $problemCodes->code_system = '2.16.840.1.113883.6.3';
                    if ( !empty($potentialICD10List[ 0 ]) ) $problemCodes->code = $potentialICD10List[ 0 ];
                }

                foreach ( $cpmProblems as $cpmProblem ) {
                    /**
                     * Since we are doing string comparison, I10 != I10.0
                     * This is is to prevent this
                     */
                    if ( !strpos( $problemCodes->code, '.' ) ) {
                        $problemCodes->code .= '.0';
                    }

                    if ( (string)$problemCodes->code >= (string)$cpmProblem->icd10from
                        && (string)$problemCodes->code <= (string)$cpmProblem->icd10to
                    ) {
                        $this->activateCPProblem( $cpmProblem->name );
                        break;
                    }
                }

                continue;
            }

            /*
             * Try to match keywords
             */
            foreach ( $cpmProblems as $cpmProblem ) {
                $keywords = explode( ',', $cpmProblem->contains );

                foreach ( $keywords as $keyword ) {
                    if ( empty($keyword) ) continue;

                    if ( strpos( $problemCodes->name, $keyword ) ) {
                        $this->activateCPProblem( $cpmProblem->name );
                        continue;
                    }
                }
            }
        }
    }

    private function activateCPProblem($cpmProblemName)
    {
        if ( empty($this->blogId) or empty($this->userId) ) throw new \Exception( 'UserID and BlogID are required.' );

        $pcp = CPRulesPCP::whereProvId( $this->blogId )->whereSectionText( 'Diagnosis / Problems to Monitor' )->first();
        if ( empty($pcp) ) {
            Log::error( __METHOD__ . ' ' . __LINE__ . ' for userID ' . $this->userId . ', blogId ' . $this->blogId . ' has failed.' );
            return;
        }
        $pcpId = $pcp->pcp_id;

        $rulesItem = CPRulesItem::wherePcpId( $pcpId )->whereItemsText( $cpmProblemName )->first();
        if ( empty($rulesItem) ) {
            Log::error( __METHOD__ . ' ' . __LINE__ . ' for userID ' . $this->userId . ', blogId ' . $this->blogId . ' has failed.' );
            return;
        }
        $parentItemId = $rulesItem->items_id;

        //Set UI Item to Active
        CPRulesUCP::updateOrCreate( [
            'items_id' => $parentItemId,
            'user_id' => $this->userId,
            'meta_key' => 'status',
        ], [
            'meta_value' => 'Active',
        ] );
    }


    /**
     * Consolidate Problem info from BB Problem and BB Problem Translation sections.
     * Sometimes info is in problem, or translation or both.
     *
     * Overwrite the problem section with the preferred one.
     *
     * @param $ccdProblem
     * @return mixed
     */
    private function consolidateProblemInfo($ccdProblem)
    {
        $consolidatedProblem = $ccdProblem;

        if ( !empty($ccdProblem->translation->code) ) {
            $consolidatedProblem->code = $ccdProblem->translation->code;
            $consolidatedProblem->code_system = $ccdProblem->translation->code_system;
            $consolidatedProblem->code_system_name = $ccdProblem->translation->code_system_name;
        }

        if ( empty($consolidatedProblem->name) && !empty($ccdProblem->translation->name) ) {
            $consolidatedProblem->name = $ccdProblem->translation->name;
        }

        return $consolidatedProblem;
    }
}