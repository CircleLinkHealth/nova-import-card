<?php

namespace App\CLH\CCD\Importer\StorageStrategies\DefaultSections;


use App\CLH\CCD\Importer\StorageStrategies\BaseStorageStrategy;
use App\CLH\Contracts\CCD\DefaultSectionsImporter;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use Illuminate\Support\Facades\Log;

class TransitionalCare extends BaseStorageStrategy implements DefaultSectionsImporter
{
    public function setDefaults()
    {
        if ( empty($this->blogId) or empty($this->userId) ) throw new \Exception( 'UserID and BlogID are required.' );

        $pcp = CPRulesPCP::whereProvId( $this->blogId )->whereSectionText( 'Transitional Care Management' )->first();
        if ( empty($pcp) ) {
            Log::error( __METHOD__ . ' ' . __LINE__ . ' for userID ' . $this->userId . ', blogId ' . $this->blogId . ' has failed.' );
            return;
        }
        $pcpId = $pcp->pcp_id;

        $rulesItem = CPRulesItem::wherePcpId( $pcpId )->whereItemsText( 'Track Care Transitions' )->first();
        if ( empty($rulesItem) ) {
            Log::error( __METHOD__ . ' ' . __LINE__ . ' for userID ' . $this->userId . ', blogId ' . $this->blogId . ' has failed.' );
            return;
        }
        $parentItemId = $rulesItem->items_id;

        $details = CPRulesItem::wherePcpId( $pcpId )->whereItemsParent( $parentItemId )->whereItemsText( 'Contact Days' )->first();
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
            'meta_value' => 5,
        ] );
    }
}