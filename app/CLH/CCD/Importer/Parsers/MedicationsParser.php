<?php

namespace App\CLH\CCD\Importer\Parsers;

use App\CLH\CCD\APILookups\Medications\RxNORM;
use App\CLH\Contracts\CCD\Parser;
use App\CLH\Facades\StringManipulation;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MedicationsParser extends BaseParser implements Parser
{
    public function parse()
    {
        $medications = $this->ccd->medications;
        $importIfEndDateIsNull = $this->importIfEndDateIsNullAndStartDateExists();

        $medsList = '';
        foreach ($medications as $medication) {
            $endDate = '';

            if (! empty($medication->date_range->end)) {
                $endDate = Carbon::createFromTimestamp( strtotime( $medication->date_range->end ) );
            }

            if ( (! empty($endDate) && ! $endDate->isPast())
                || ($importIfEndDateIsNull && ! empty($medication->date_range->start) && empty($medication->date_range->end)))
            {
                empty($medication->product->name)
                    ? $medsList .= ''
                    : $medsList .= ucfirst(strtolower($medication->product->name)) . ', ';

                $medsList .= ucfirst(strtolower(StringManipulation::stringDiff($medication->product->name, $medication->text)))
                    . "; \n\n";
            }
            elseif (strtolower($medication->status) == 'active')
            {
                empty($medication->product->name)
                    ? $medsList .= ''
                    : $medsList .= ucfirst(strtolower($medication->product->name)) . ', ';

                $medsList .= ucfirst(strtolower(StringManipulation::stringDiff($medication->product->name, $medication->text)))
                    . "; \n\n";
            }
        }
        return $medsList;
    }

    public function save($medsList)
    {
        if (empty($this->blogId) or empty($this->userId)) throw new \Exception('UserID and BlogID are required.');

        if (empty($medsList)) return;

        $pcp = CPRulesPCP::whereProvId($this->blogId)->whereSectionText('Medications to Monitor')->first();
        if (empty($pcp)) {
            Log::error('Medication import for userID ' . $this->userId . ', blogId ' . $this->blogId . ' has failed.'
                . ' $pcp was empty in ' . self::class . '@createOrUpdateMedicationsList()');
            return;
        }
        $pcpId = $pcp->pcp_id;

        $medListPCP = CPRulesItem::wherePcpId($pcpId)->whereItemsText('Medication List')->first();
        if (empty($medListPCP)) {
            Log::error('Medication import for userID ' . $this->userId . ', blogId ' . $this->blogId . ' has failed.'1
                . ' $medListPCP was empty in ' . self::class . '@createOrUpdateMedicationsList()');
            return;
        }
        $parentItemId = $medListPCP->items_id;

        $details = CPRulesItem::wherePcpId($pcpId)->whereItemsParent($parentItemId)->whereItemsText('Details')->first();
        if (empty($details)) {
            Log::error('Medication import for userID ' . $this->userId . ', blogId ' . $this->blogId . ' has failed.'
                . ' $details was empty in ' . self::class . '@createOrUpdateMedicationsList()');
            return;
        }
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
            'meta_value' => $medsList,
        ]);
    }

    public $oidMap = [
        RxNORM::class => '2.16.840.1.113883.6.88',
        'NDC' => '2.16.840.1.113883.6.69',
        'SNOMED CT' => '2.16.840.1.113883.6.96'
    ];

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