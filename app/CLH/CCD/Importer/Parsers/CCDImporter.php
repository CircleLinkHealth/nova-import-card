<?php

namespace App\CLH\CCD\Importer\Parsers;

use App\CLH\DataTemplates\UserConfigTemplate;
use App\CLH\DataTemplates\UserMetaTemplate;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use Illuminate\Support\Facades\Log;

class CCDImporter extends BaseParser
{
    public function generateCarePlanFromCCD()
    {
        $blogId = $this->blogId;
        $parsedCCD = $this->parsedCcdObj;

        /**
         * Import Allergies
         */
        $allergiesParser = new AllergiesParser($blogId, $parsedCCD);
        $allergiesList = $allergiesParser->parse();
        $allergiesParser->save($allergiesList);

        /**
         * Import Medications
         */
        $medsParser = new MedicationsParser($blogId, $parsedCCD);
        $medsList = $medsParser->parse();
        $medsParser->save($medsList);

        /**
         * Import Problems
         */
        $problemsParser = new ProblemsParser($blogId, $parsedCCD);
        $problemsList = $problemsParser->parse();
        $problemsParser->save($problemsList);

        $problemsParser->activateCPProblems();

        /**
         * Import User Config
         */
        $userConfigParser = new UserConfigParser($blogId, $parsedCCD, new UserConfigTemplate());
        $userConfig =  $userConfigParser->parse()->getArray();
        $userConfigParser->save($userConfig);

        /**
         * Import User Meta
         */
        $userMetaParser = new UserMetaParser($blogId, $parsedCCD, new UserMetaTemplate());
        $userMeta = $userMetaParser->parse()->getArray();
        $userMetaParser->save($userMeta);

        /**
         * CarePlan Defaults
         */
        $this->setTransitionalCareDefaults();
    }

    public function setTransitionalCareDefaults()
    {
        if (empty($this->blogId) or empty($this->userId)) throw new \Exception('UserID and BlogID are required.');

        $pcp = CPRulesPCP::whereProvId($this->blogId)->whereSectionText('Transitional Care Management')->first();
        if (empty($pcp)) {
            Log::error(__METHOD__ . ' ' . __LINE__ . ' for userID ' . $this->userId . ', blogId ' . $this->blogId . ' has failed.');
            return;
        }
        $pcpId = $pcp->pcp_id;

        $rulesItem = CPRulesItem::wherePcpId($pcpId)->whereItemsText('Track Care Transitions')->first();
        if (empty($rulesItem)) {
            Log::error( __METHOD__ . ' ' . __LINE__ . ' for userID ' . $this->userId . ', blogId ' . $this->blogId . ' has failed.');
            return;
        }
        $parentItemId = $rulesItem->items_id;

        $details = CPRulesItem::wherePcpId($pcpId)->whereItemsParent($parentItemId)->whereItemsText('Contact Days')->first();
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
            'meta_value' => 5,
        ]);
    }
}