<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExportPamEndOfMonthReport extends CustomDownloadExcel implements WithMapping
{
    use InteractsWithQueue;
    use Queueable;

    protected $headings = [
        'Nurse Name',
        'Patient Name',
        'Practice',
        'Last Call',
        'CCM Time',
        'CCM (RHC/FQHC) Time',
        'PCM Time',
        'BHI Time',
        'RPM Time',
        'Successful Calls',
    ];

    public function map($row): array
    {
        $result = [];

        foreach ($this->headings() as $heading) {
            $result[] = $row[$heading];
        }

        return $result;
    }
}
