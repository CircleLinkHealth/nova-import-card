<?php

namespace App\CLH\CCD;

use App\CLH\CCD\QAImportSummary;


trait ValidatesQAImportOutput
{
    public function validateQAImportOutput($output, Ccda $ccda)
    {
        $jsonCcd = $output;

        $removeDuplicateMeds = function () use ($jsonCcd) {
            $medications = explode( ';', $jsonCcd[ 3 ] );
        };
//        $removeDuplicateMeds();

        $name = function () use ($jsonCcd) {
            return empty($name = $jsonCcd[ 'userMeta' ]->first_name . ' ' . $jsonCcd[ 'userMeta' ]->last_name)
                ?: $name;
        };

        $provider = function () use ($jsonCcd) {
            if (isset($jsonCcd[ 'provider' ][0])) return $jsonCcd[ 'provider' ][0]['display_name'];
        };

        $location = function () use ($jsonCcd) {
          if (isset($jsonCcd[ 'location' ][0])) return $jsonCcd[ 'location' ][0]['name'];
        };

        $counter = function ($index) use ($jsonCcd) {
            return count( $jsonCcd[ $index ] );
        };

        $qaSummary = new QAImportSummary();
        $qaSummary->ccda_id = $ccda->id;
        $qaSummary->name = $name();
        $qaSummary->medications = $counter( 3 );
        $qaSummary->problems = $counter( 1 );
        $qaSummary->allergies = $counter( 0 );
        $qaSummary->provider = $provider();
        $qaSummary->location = $location();
        $qaSummary->save();

        return $qaSummary;
    }

}