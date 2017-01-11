<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Demographics;


use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;
use App\CLH\DataTemplates\UserConfigTemplate;
use App\Importer\Models\ItemLogs\DemographicsLog;
use App\Models\MedicalRecords\Ccda;
use Carbon\Carbon;

class UserConfig implements ParsingStrategy
{
    private $blogId;
    private $template;

    public function __construct(UserConfigTemplate $template, $blogId)
    {
        $this->blogId = $blogId;
        $this->template = $template;
    }

    public function parse(Ccda $ccd, ValidationStrategy $validator = null)
    {
        $demographicsSection = DemographicsLog::whereCcdaId($ccd->id)->first();

        $this->template->email = $demographicsSection->email;
        $this->template->mrn_number = $demographicsSection->mrn_number;

        $this->template->home_phone_number = $demographicsSection->home_phone;
        $this->template->mobile_phone_number = $demographicsSection->cell_phone;
        $this->template->work_phone_number = $demographicsSection->work_phone;

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

            return empty($gender) ?: $gender;
        } );
        $this->template->address = empty($demographicsSection->street2) ? $demographicsSection->street : $demographicsSection->street . '' . $demographicsSection->street2;
        $this->template->city = $demographicsSection->city;
        $this->template->state = $demographicsSection->state;
        $this->template->zip = $demographicsSection->zip;
        $this->template->birth_date = ( new Carbon( $demographicsSection->dob, 'America/New_York' ) )->format( 'Y-m-d' );

        $this->template->preferred_contact_language = call_user_func( function () use ($demographicsSection) {
            $englishVariations = ['english', 'eng', 'en', 'e'];

            $spanishVariations = ['spanish', 'es'];

            $default = 'EN';

            if ( in_array( strtolower( $demographicsSection->preferred_contact_language ), $englishVariations ) ) {
                $language = 'EN';
            }
            else if ( in_array( strtolower( $demographicsSection->preferred_contact_language ), $spanishVariations ) ) {
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