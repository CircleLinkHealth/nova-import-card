<?php

namespace App\CLH\CCD\Importer\Parsers\Helpers;

use App\CLH\CCD\APILookups\Medications\RxNORM;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\ParameterBag;

class MedicationsParserHelpers
{
    public $oidMap = [
        RxNORM::class => '2.16.840.1.113883.6.88',
        'NDC' => '2.16.840.1.113883.6.69',
        'SNOMED CT' => '2.16.840.1.113883.6.96'
    ];

    public function createOrUpdateMedicationsList($userId, $blogId, $medsList)
    {
        if (empty($blogId) or empty($userId)) throw new \Exception('UserID and BlogID are required.');

        if (empty($medsList)) return;

        $pcp = CPRulesPCP::whereProvId($blogId)->whereSectionText('Additional Information')->first();
        $pcpId = $pcp->pcp_id;
        $medListPCP = CPRulesItem::wherePcpId($pcpId)->whereItemsText('Medications List')->first();
        $parentItemId = $medListPCP->items_id;
        $details = CPRulesItem::wherePcpId($pcpId)->whereItemsParent($parentItemId)->whereItemsText('Details')->first();
        $itemId = $details->items_id;
        //UI Item
        CPRulesUCP::updateOrCreate([
            'items_id' => $parentItemId,
            'user_id' => $userId,
            'meta_key' => 'status',
        ], [
            'meta_value' => 'Active',
        ]);
        //Value
        CPRulesUCP::updateOrCreate([
            'items_id' => $itemId,
            'user_id' => $userId,
            'meta_key' => 'value',
        ], [
            'meta_value' => $medsList,
        ]);
    }

    public function import($userId, $blogId, $medications)
    {
        $medsList = '';
        foreach ($medications as $medication) {
            $endDate = Carbon::createFromTimestamp(strtotime($medication->date_range->end));
            if (! $endDate->isPast()) {
                if (! empty($med = $this->medicationLookup($medication))) {
                    $medsList .= ucwords(strtolower($med)) . ", \n";
                }
            }
        }

        $this->createOrUpdateMedicationsList($userId, $blogId, $medsList);
    }

    public function medicationLookup($medication)
    {
        $medName = explode(' ', $medication->product->name);

        if (empty($medName[0])) return;

        $med = $this->oidLookup($medication->product->code_system);

        return (new $med())->findByName($medName[0]);
    }

    public function oidLookup($code)
    {
        $className = array_search($code, $this->oidMap);

        return $className;
    }

}