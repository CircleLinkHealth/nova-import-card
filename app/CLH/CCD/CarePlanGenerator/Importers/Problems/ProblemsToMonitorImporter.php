<?php

namespace App\CLH\CCD\CarePlanGenerator\Importers\Problems;


use App\CLH\CCD\CarePlanGenerator\Importers\BaseImporter;
use App\CLH\Contracts\CCD\Importer;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use Illuminate\Support\Facades\Log;

class ProblemsToMonitorImporter extends BaseImporter implements Importer
{
    public function import($cpmProblemNames = [])
    {
        if (empty($cpmProblemNames)) return;

        foreach ($cpmProblemNames as $cpmProblemName)
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
    }
}