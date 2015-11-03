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

        return $meta->getArray();
    }

    public function parseUserConfig(UserConfigTemplate $contact)
    {
        $demographics = $this->ccd->demographics;

        $contact->email = $demographics->email;
        $contact->mrn_number = ''; //@todo
        $contact->study_phone_number = $demographics->phone->mobile;
        $contact->gender = call_user_func(function () use ($demographics) {
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
        $contact->address = $demographics->address->street[0];
        $contact->city = $demographics->address->city;
        $contact->state = $demographics->address->state;
        $contact->zip = $demographics->address->zip;
        $contact->birth_date = (new Carbon($demographics->dob))->format('Y-m-d');

        return $contact->getArray();
    }

}