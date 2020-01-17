<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\CCD\Importer\ParsingStrategies\Helpers;

use CircleLinkHealth\Core\StringManipulation;

class UserMetaParserHelpers
{
    /**
     * Returns formatted phone numbers, organized by type ('home', 'mobile' etc).
     *
     * @param array $phones
     *
     * @return array
     */
    public function getAllPhoneNumbers($phones = [])
    {
        $home    = [];
        $mobile  = [];
        $work    = [];
        $primary = [];

        foreach ($phones as $phone) {
            if ( ! isset($phone->number)) {
                continue;
            }

            $type = isset($phone->type)
                ? $phone->type
                : 'home';

            if ( ! $number = (new \CircleLinkHealth\Core\StringManipulation())->formatPhoneNumber($phone->number)) {
                continue;
            }

            switch ($type) {
                case 'home':
                    array_push($home, $number);
                    break;
                case 'mobile':
                    array_push($mobile, $number);
                    break;
                case 'work':
                    array_push($work, $number);
                    break;
                case 'primary_phone':
                    array_push($primary, $number);
                    break;
            }
        }

        $phoneCollections = compact('home', 'mobile', 'work', 'primary');

        foreach ($phoneCollections as $key => $phoneCollection) {
            if (empty($phoneCollection)) {
                array_push($phoneCollections[$key], null);
            }
        }

        return $phoneCollections;
    }
}
