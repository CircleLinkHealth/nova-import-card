<?php

namespace App\CLH\CCD;

use App\CLH\CCD\QAImportSummary;


trait ValidatesQAImportOutput
{
    public function validateQAImportOutput(QAImportOutput $output)
    {
        $jsonCcd = json_decode( $output->output, true );

        $removeDuplicateMeds = function () use ($jsonCcd) {
            $medications = explode( ';', $jsonCcd[ 3 ] );
        };
        $removeDuplicateMeds();

        $name = function () use ($jsonCcd) {
            return empty($name = $jsonCcd[ 'userMeta' ][ 'first_name' ] . ' ' . $jsonCcd[ 'userMeta' ][ 'last_name' ])
                ?: $name;
        };

        $provider = function () use ($jsonCcd) {
            if (isset($jsonCcd[ 'provider' ][0])) return $jsonCcd[ 'provider' ][0]['display_name'];
        };

        $counter = function ($index) use ($jsonCcd) {
            return count( explode( ';', $jsonCcd[ $index ] ) ) - 1;
        };

        $qaSummary = new QAImportSummary();
        $qaSummary->qa_output_id = $output->id;
        $qaSummary->name = $name();
        $qaSummary->medications = $counter( 3 );
        $qaSummary->problems = $counter( 1 );
        $qaSummary->allergies = $counter( 0 );
        $qaSummary->provider = $provider();
        $qaSummary->save();

        return $qaSummary;
    }

}