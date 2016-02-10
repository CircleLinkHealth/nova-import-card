<?php

namespace App\CLH\CCD\Importer\Validators;


use App\CLH\CCD\Importer\Validators\BaseValidator;
use App\CLH\CCD\Importer\Parsers\Facades\UserMetaParserHelpers;
use App\CLH\Contracts\CCD\Parser;
use App\CLH\Repositories\WpUserRepository;
use App\WpUser;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\ParameterBag;

class UserConfigValidator extends BaseValidator implements Parser
{
    public function parse()
    {
        $userConfig = $this->meta;

        $demographics = $this->ccd->demographics;

        $userConfig->email = $demographics->email;
        $userConfig->mrn_number = $demographics->mrn_number;

        $phones = UserMetaParserHelpers::getAllPhoneNumbers( $demographics->phones );

        $userConfig->home_phone_number = $phones[ 'home' ][ 0 ];
        $userConfig->mobile_phone_number = $phones[ 'mobile' ][ 0 ];
        $userConfig->work_phone_number = $phones[ 'work' ][ 0 ];

        //primary phone number
        $userConfig->study_phone_number =
            empty($userConfig->mobile_phone_number)
                ? empty($userConfig->home_phone_number)
                    ? $userConfig->work_phone_number
                    : $userConfig->home_phone_number
                : $userConfig->mobile_phone_number;

        $userConfig->gender = call_user_func( function () use ($demographics) {
            $maleVariations = ['m', 'male', 'man'];

            $femaleVariations = ['f', 'female', 'woman'];

            if ( in_array( strtolower( $demographics->gender ), $maleVariations ) ) {
                $gender = 'M';
            }
            else if ( in_array( strtolower( $demographics->gender ), $femaleVariations ) ) {
                $gender = 'F';
            }

            return empty($gender) ? null : $gender;
        } );
        $userConfig->address = call_user_func( function () use ($demographics) {
            $street = $demographics->address->street;
            if ( array_key_exists( 1, $street ) ) return $street[ 0 ] . ', ' . $street[ 1 ];
            if ( array_key_exists( 0, $street ) ) return $street[ 0 ];
            return false;
        } );
        $userConfig->city = $demographics->address->city;
        $userConfig->state = $demographics->address->state;
        $userConfig->zip = $demographics->address->zip;
        $userConfig->birth_date = ( new Carbon( $demographics->dob, 'America/New_York' ) )->format( 'Y-m-d' );

        $userConfig->preferred_contact_language = call_user_func( function () use ($demographics) {
            $englishVariations = ['english', 'eng', 'en', 'e'];

            $spanishVariations = ['spanish', 'es'];

            $default = 'EN';

            if ( in_array( strtolower( $demographics->language ), $englishVariations ) ) {
                $language = 'EN';
            }
            else if ( in_array( strtolower( $demographics->language ), $spanishVariations ) ) {
                $language = 'ES';
            }

            return empty($language) ? $default : $language;
        } );

        $userConfig->consent_date = date( "Y-m-d" );

        $userConfig->preferred_contact_timezone = call_user_func( function () use ($userConfig) {
            $zip = $userConfig->zip;
            $default = 'America/New_York';

            /**
             * TimeZone lookup goes here
             */
            $timezone = '';

            return empty($timezone) ? $default : $timezone;
        } );

        $userConfig->program_id = $this->blogId;

        return $userConfig;
    }

    public function save($data)
    {
        $userRepo = new WpUserRepository();
        $wpUser = WpUser::find($this->userId);
        $userRepo->updateUserConfig($wpUser, new ParameterBag($data));
    }
}