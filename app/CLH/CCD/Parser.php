<?php

namespace App\CLH\CCD;


use App\CLH\DataTemplates\UserConfigTemplate;
use App\CLH\DataTemplates\UserMetaTemplate;
use App\ParsedCCD;
use App\WpUser;
use Carbon\Carbon;

class Parser
{

    public $ccd;

    public function __construct(ParsedCCD $ccd)
    {
        $this->ccd = json_decode($ccd->ccd);
    }

    public function parseUserMeta(UserMetaTemplate $meta)
    {
        $demographics = $this->ccd->demographics;

        $meta->first_name = $demographics->name->given[0];
        $meta->last_name = $demographics->name->family;
        $meta->nickname = "$meta->first_name $meta->last_name";

        return $meta;
    }

    public function parseUserConfig(UserConfigTemplate $userConfig)
    {
        $demographics = $this->ccd->demographics;

        $userConfig->email = $demographics->email;
        $userConfig->mrn_number = ''; //@todo
        $userConfig->study_phone_number = $demographics->phone->mobile;
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
            $englishVariations = ['english', 'eng', 'en'];

            $spanishVariations = ['spanish', 'es'];

            if (in_array(strtolower($demographics->language), $englishVariations))
            {
                $language = 'EN';
            }
            else if (in_array(strtolower($demographics->language), $spanishVariations))
            {
                $language = 'ES';
            }

            return empty($language) ? null : $language;
        });


        return $userConfig;
    }

}