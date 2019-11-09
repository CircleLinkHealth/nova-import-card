<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\GeneratePatientCallDataCsv;

use App\Exports\FromArray;

class GeneratePatientCallDataCsvController
{
    public function handle($month)
    {
        $fileName = 'test.xls';

        $headings = [
            '1',
            '2',
            '3',
            '4',
            '5',
        ];

        $rows = [
            ['a', 'b', 'c', 'd', 'e'],
            ['a1', 'b1', 'c1', 'd1', 'e1'],
            ['a2', 'b2', 'c2', 'd2', 'e2'],
        ];

        return (new FromArray($fileName, $rows, $headings))->download($fileName);
        //return media download
    }

    private function getData()
    {
        //for current month get from calls view
        //for the past create custom query
        return [];
    }
}
