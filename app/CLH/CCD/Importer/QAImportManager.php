<?php

namespace App\CLH\CCD\Importer;


use App\CLH\CCD\Ccda;
use App\CLH\CCD\Importer\ParsingStrategies\CareTeam\PrimaryProviderParser;
use App\CLH\CCD\Importer\ParsingStrategies\Problems\DetectsProblemCodeSystemTrait;
use App\CLH\CCD\Importer\StorageStrategies\DefaultSections\TransitionalCare;
use App\CLH\CCD\Importer\StorageStrategies\Demographics\UserConfigStorageStrategy;
use App\CLH\CCD\Importer\StorageStrategies\Demographics\UserMetaStorageStrategy;
use App\CLH\CCD\Importer\ParsingStrategies\Demographics\UserConfigParser;
use App\CLH\CCD\Importer\ParsingStrategies\Demographics\UserMetaParser;
use App\CLH\CCD\ImportRoutine\ExecutesImportRoutine;
use App\CLH\CCD\ImportRoutine\RoutineBuilder;
use App\CLH\CCD\QAImportOutput;
use App\CLH\CCD\Vendor\CcdVendor;
use App\CLH\DataTemplates\UserConfigTemplate;
use App\CLH\DataTemplates\UserMetaTemplate;
use App\ParsedCCD;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class QAImportManager
{
    use DetectsProblemCodeSystemTrait;
    use ExecutesImportRoutine;

    private $blogId;
    private $ccd;
    private $routine;

    public function __construct($blogId, Ccda $ccda)
    {
        $this->blogId = $blogId;
        $this->ccda = $ccda;
        $this->ccd = json_decode( $ccda->json );

        $this->routine = empty($ccda->vendor_id)
            ? ( new RoutineBuilder( $this->ccd ) )->getRoutine()
            : CcdVendor::find( $ccda->vendor_id )->routine()->first()->strategies()->get();

        Log::info( 'Routine ID: ' . $this->routine[ 0 ]->ccd_import_routine_id . ' ' . $this->ccd->demographics->name->family );
    }

    public function generateCarePlanFromCCD()
    {
        $strategies = \Config::get( 'ccdimporterstrategiesmaps' );

        $output = [];

        /**
         * Temporary fix for Middletown CCDs
         * They say problem codes are ICD-10, but they are ICD-9
         */
        if ( $this->ccda->vendor_id == 9 ) {
            foreach ( $this->ccd->problems as $problem ) {
                if ( $problem->code_system == '2.16.840.1.113883.6.4' ) {
                    $problem->code_system = '2.16.840.1.113883.6.103';
                    $problem->code_system_name = 'ICD-9';
                }
            }
        }

        /**
         * Parse and Import Allergies List, Medications List, Problems List, Problems To Monitor
         */
        foreach ( $this->routine as $routine ) {
            $validator = new $strategies[ 'validation' ][ $routine->validator_id ]();
            $parser = new $strategies[ 'parsing' ][ $routine->parser_id ]();
            $output[ $routine->importer_section_id ] = $parser->parse( $this->ccd, $validator );
        }

        /**
         * The following Sections are the same for each CCD
         */

        /**
         * Parse and Import User Meta
         */
        $userMetaParser = new UserMetaParser( new UserMetaTemplate() );
        $output[ 'userMeta' ] = $userMetaParser->parse( $this->ccd );


        /**
         * Find provider
         */
        $primaryProviderParser = new PrimaryProviderParser();
        $output[ 'provider' ] = $primaryProviderParser->parse( $this->ccd->document->documentation_of );

        /**
         * Parse and Import User Config
         */
        $userConfigParser = new UserConfigParser( new UserConfigTemplate(), $this->blogId );
        $output[ 'userConfig' ] = $userConfigParser->parse( $this->ccd );

        return QAImportOutput::create( [
            'ccda_id' => $this->ccda->id,
            'output' => json_encode( $output, JSON_FORCE_OBJECT ),
        ] );
    }
}