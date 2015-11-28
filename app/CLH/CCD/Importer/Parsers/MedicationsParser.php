<?php

namespace App\CLH\CCD\Importer\Parsers;

use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;

class MedicationsParser extends BaseParser
{
    /**
     * THIS IS GROSS. NEEDS REFACTORING.
     * Updates Medications List
     */
    public function parse()
    {
        $pcp = CPRulesPCP::whereProvId($this->blogId)->whereSectionText('Additional Information')->first();
        $pcpId = $pcp->pcp_id;
        $medListPCP = CPRulesItem::wherePcpId($pcpId)->whereItemsText('Medications List')->first();
        $parentItemId = $medListPCP->items_id;
        $details = CPRulesItem::wherePcpId($pcpId)->whereItemsParent($parentItemId)->whereItemsText('Details')->first();
        $itemId = $details->items_id;
        //UI Item
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
            'meta_value' => 'Michalis testing',
        ]);
    }
}