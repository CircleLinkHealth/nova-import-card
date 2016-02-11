<?php

namespace App\CLH\CCD\CarePlanGenerator\Importers\Medications;


use App\CLH\CCD\CarePlanGenerator\Importers\BaseImporter;
use App\CLH\Contracts\CCD\Importer;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use Illuminate\Support\Facades\Log;

class MedicationsListImporter extends BaseImporter implements Importer
{

    public function import($medsList)
    {
        if ( empty($this->blogId) or empty($this->userId) ) throw new \Exception( 'UserID and BlogID are required.' );

        if ( empty($medsList) ) return;

        $pcp = CPRulesPCP::whereProvId( $this->blogId )->whereSectionText( 'Medications to Monitor' )->first();
        if ( empty($pcp) ) {
            Log::error( 'Medication import for userID ' . $this->userId . ', blogId ' . $this->blogId . ' has failed.'
                . ' $pcp was empty in ' . self::class . '@createOrUpdateMedicationsList()' );
            return;
        }
        $pcpId = $pcp->pcp_id;

        $medListPCP = CPRulesItem::wherePcpId( $pcpId )->whereItemsText( 'Medication List' )->first();
        if ( empty($medListPCP) ) {
            Log::error( 'Medication import for userID ' . $this->userId . ', blogId ' . $this->blogId . ' has failed.'
                . ' $medListPCP was empty in ' . self::class . '@createOrUpdateMedicationsList()' );
            return;
        }
        $parentItemId = $medListPCP->items_id;

        $details = CPRulesItem::wherePcpId( $pcpId )->whereItemsParent( $parentItemId )->whereItemsText( 'Details' )->first();
        if ( empty($details) ) {
            Log::error( 'Medication import for userID ' . $this->userId . ', blogId ' . $this->blogId . ' has failed.'
                . ' $details was empty in ' . self::class . '@createOrUpdateMedicationsList()' );
            return;
        }
        $itemId = $details->items_id;
        //UI Item
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
            'meta_value' => $medsList,
        ] );
    }
}