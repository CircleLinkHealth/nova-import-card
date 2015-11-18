<?php

namespace App\CLH\CCD\Importer\Parsers\Helpers;

use App\CLH\Facades\StringManipulation;

class UserMetaParserHelpers
{
    /**
     * Returns formatted phone numbers, organized by type ('home', 'mobile' etc).
     *
     * @param array $phones
     * @return array
     */
    public function getAllPhoneNumbers($phones = [])
    {
        $home = [];
        $mobile = [];
        $work = [];

        foreach ($phones as $phone)
        {
            $type = $phone->type;
            $number = StringManipulation::formatPhoneNumber($phone->number);

            if ($type == 'home')
            {
                array_push($home, $number);
            }
            else if ($type == 'mobile')
            {
                array_push($mobile, $number);
            }
            else if ($type == 'work')
            {
                array_push($work, $number);
            }
        }

        $phoneCollections = compact('home', 'mobile', 'work');

        foreach ($phoneCollections as $key => $phoneCollection)
        {
            if (empty($phoneCollection))
            {
                array_push($phoneCollections[$key], null);
            }
        }

        return $phoneCollections;
    }

}