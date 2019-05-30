<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class NurseInvoiceCsv implements FromCollection
{
    protected $date;

    public function __construct(Carbon $date)
    {
        $this->date = $date;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        // TODO: Implement collection() method.
    }
}
