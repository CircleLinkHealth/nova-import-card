<?php

namespace App\CLH\CCD\Importer\Parsers;

use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use Illuminate\Support\Facades\Log;

class AllergiesParser extends BaseParser
{
    public function parse()
    {
        $ccdAllergies = $this->ccd->allergies;
        $importIfEndDateIsNull = $this->importIfEndDateIsNullAndStartDateExists();
        $allergiesList = '';

        foreach ($ccdAllergies as $ccdAllergy)
        {
            /**
             * Import only active allergies, unless this is a CCD we can $importIfEndDateIsNull
             */
            if (! $importIfEndDateIsNull) {
                if (! in_array(strtolower($ccdAllergy->status), ['active'])) continue;
            }

            /**
             * Special case CCDs can only be imported if there is a start date, but no end date.
             * See importIfEndDateIsNullAndStartDateExists()
             */
            if ($importIfEndDateIsNull
                && (empty($ccdAllergy->date_range->start) || ! empty($ccdAllergy->date_range->end))) continue;

            $ccdAllergen = $ccdAllergy->allergen;

            if (empty($ccdAllergen->name)) continue;

            $allergiesList .= $ccdAllergen->name . ";\n\n";
        }

        return $allergiesList;
    }

    public function save($allergiesList)
    {
        if (empty($allergiesList)) return;

        if (empty($this->blogId) or empty($this->userId)) throw new \Exception('UserID and BlogID are required.');

        $pcp = CPRulesPCP::whereProvId($this->blogId)->whereSectionText('Additional Information')->first();
        if (empty($pcp)) {
            Log::error(__METHOD__ . ' ' . __LINE__ . ' for userID ' . $this->userId . ', blogId ' . $this->blogId . ' has failed.');
            return;
        }
        $pcpId = $pcp->pcp_id;

        $rulesItem = CPRulesItem::wherePcpId($pcpId)->whereItemsText('Allergies')->first();
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
            'meta_value' => $allergiesList,
        ]);
    }
}