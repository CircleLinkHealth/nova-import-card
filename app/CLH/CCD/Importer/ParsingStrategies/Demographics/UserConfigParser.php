<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Demographics;


use App\CLH\CCD\Importer\ParsingStrategies\Facades\UserMetaParserHelpers;
use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;
use App\CLH\DataTemplates\UserConfigTemplate;
use Carbon\Carbon;

class UserConfigParser implements ParsingStrategy
{
    private $blogId;
    private $template;

    public function __construct(UserConfigTemplate $template, $blogId)
    {
        $this->blogId = $blogId;
        $this->template = $template;
    }

    public function parse($ccd, ValidationStrategy $validator = null)
    {
        $demographicsSection = $ccd->demographics;

        $this->template->email = $demographicsSection->email;
        $this->template->mrn_number = $demographicsSection->mrn_number;

        $phones = UserMetaParserHelpers::getAllPhoneNumbers( $demographicsSection->phones );

        $this->template->home_phone_number = $phones[ 'home' ][ 0 ];
        $this->template->mobile_phone_number = $phones[ 'mobile' ][ 0 ];
        $this->template->work_phone_number = $phones[ 'work' ][ 0 ];

        //primary phone number
        $this->template->study_phone_number =
            empty($this->template->mobile_phone_number)
                ? empty($this->template->home_phone_number)
                ? $this->template->work_phone_number
                : $this->template->home_phone_number
                : $this->template->mobile_phone_number;

        $this->template->gender = call_user_func( function () use ($demographicsSection) {
            $maleVariations = ['m', 'male', 'man'];

            $femaleVariations = ['f', 'female', 'woman'];

            if ( in_array( strtolower( $demographicsSection->gender ), $maleVariations ) ) {
                $gender = 'M';
            }
            else if ( in_array( strtolower( $demographicsSection->gender ), $femaleVariations ) ) {
                $gender = 'F';
            }

            return empty($gender) ? null : $gender;
        } );
        $this->template->address = call_user_func( function () use ($demographicsSection) {
            $street = $demographicsSection->address->street;
            if ( array_key_exists( 1, $street ) ) return $street[ 0 ] . ', ' . $street[ 1 ];
            if ( array_key_exists( 0, $street ) ) return $street[ 0 ];
            return false;
        } );
        $this->template->city = $demographicsSection->address->city;
        $this->template->state = $demographicsSection->address->state;
        $this->template->zip = $demographicsSection->address->zip;
        $this->template->birth_date = ( new Carbon( $demographicsSection->dob, 'America/New_York' ) )->format( 'Y-m-d' );

        $this->template->preferred_contact_language = call_user_func( function () use ($demographicsSection) {
            $englishVariations = ['english', 'eng', 'en', 'e'];

            $spanishVariations = ['spanish', 'es'];

            $default = 'EN';

            if ( in_array( strtolower( $demographicsSection->language ), $englishVariations ) ) {
                $language = 'EN';
            }
            else if ( in_array( strtolower( $demographicsSection->language ), $spanishVariations ) ) {
                $language = 'ES';
            }

            return empty($language) ? $default : $language;
        } );

        $this->template->consent_date = date( "Y-m-d" );

        $this->template->preferred_contact_timezone = call_user_func( function () {
            $zip = $this->template->zip;
            $default = 'America/New_York';

            /**
             * TimeZone lookup goes here
             */
            $timezone = '';

            return empty($timezone) ? $default : $timezone;
        });

        $this->template->program_id = $this->blogId;

        return $this->template;
    }
}