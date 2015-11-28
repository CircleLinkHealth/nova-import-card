<?php

namespace App\CLH\CCD\Importer\Parsers;


use App\CLH\CCD\Importer\Parsers\Facades\UserMetaParserHelpers;
use App\CLH\DataTemplates\UserConfigTemplate;
use App\CLH\DataTemplates\UserMetaTemplate;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use App\ParsedCCD;
use App\WpUser;
use Carbon\Carbon;

class UserMetaParser
{
    public $ccd;

    protected $userId;

    public function __construct(ParsedCCD $ccd)
    {
        $this->userId = $ccd->user_id;
        $this->ccd = json_decode($ccd->ccd);
    }

    public function parseUserMeta(UserMetaTemplate $meta)
    {
        $demographics = $this->ccd->demographics;

        $meta->first_name = $demographics->name->given[0];
        $meta->last_name = $demographics->name->family;
        $meta->nickname = "";

        return $meta;
    }

    /**
     * THIS IS GROSS. NEEDS REFACTORING.
     * Updates Medications List
     */
    public function parseMedications($blogId)
    {
        $pcp = CPRulesPCP::whereProvId($blogId)->whereSectionText('Additional Information')->first();
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

    public function parseUserConfig(UserConfigTemplate $userConfig)
    {
        $demographics = $this->ccd->demographics;

        $userConfig->email = $demographics->email;
        $userConfig->mrn_number = $demographics->mrn_number;

        $phones = UserMetaParserHelpers::getAllPhoneNumbers($demographics->phones);

        $userConfig->home_phone_number = $phones['home'][0];
        $userConfig->mobile_phone_number = $phones['mobile'][0];
        $userConfig->work_phone_number = $phones['work'][0];

        //primary phone number
        $userConfig->study_phone_number =
            empty($userConfig->mobile_phone_number)
            ? empty($userConfig->home_phone_number)
                ? $userConfig->work_phone_number
                : $userConfig->home_phone_number
            : $userConfig->mobile_phone_number;

        $userConfig->gender = call_user_func(function () use ($demographics){
            $maleVariations = ['m', 'male', 'man'];

            $femaleVariations = ['f', 'female', 'woman'];

            if (in_array(strtolower($demographics->gender), $maleVariations))
            {
                $gender = 'M';
            }
            else if (in_array(strtolower($demographics->gender), $femaleVariations))
            {
                $gender = 'F';
            }

            return empty($gender) ? null : $gender;
        });
        $userConfig->address = $demographics->address->street[0];
        $userConfig->city = $demographics->address->city;
        $userConfig->state = $demographics->address->state;
        $userConfig->zip = $demographics->address->zip;
        $userConfig->birth_date = (new Carbon($demographics->dob))->format('Y-m-d');

        $userConfig->preferred_contact_language = call_user_func(function () use ($demographics){
            $englishVariations = ['english', 'eng', 'en', 'e'];

            $spanishVariations = ['spanish', 'es'];

            $default = 'EN';

            if (in_array(strtolower($demographics->language), $englishVariations))
            {
                $language = 'EN';
            }
            else if (in_array(strtolower($demographics->language), $spanishVariations))
            {
                $language = 'ES';
            }

            return empty($language) ? $default : $language;
        });

        $userConfig->consent_date = date("Y-m-d");

        $userConfig->preferred_contact_timezone = call_user_func(function () use ($userConfig){
            $zip = $userConfig->zip;
            $default = 'America/New_York';

            /**
             * TimeZone lookup goes here
             */
            $timezone = '';

            return empty($timezone) ? $default : $timezone;
        });

        return $userConfig;
    }

}