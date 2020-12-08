<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Imports\SelfEnrolment;

use Maatwebsite\Excel\Concerns\WithStartRow;
class ReadSelfEnrolmentCsvData implements WithStartRow
{
    public function startRow(): int
    {
        return 2;
    }
}
