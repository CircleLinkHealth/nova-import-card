<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Invoices;

use Carbon\Carbon;
use CircleLinkHealth\Core\Exports\FromArray;
use CircleLinkHealth\Customer\Entities\Practice;
use Spatie\MediaLibrary\Models\Media;

class GeneratePracticePatientsReport
{
    private Carbon $date;
    private array $patientsData;
    private int $practiceId;

    public function execute(): Media
    {
        $practice   = Practice::find($this->practiceId);
        $dateStr    = $this->date->toDateString();
        $reportName = trim($practice).'-'.$dateStr.'-patients';

        return (new FromArray("${reportName}.csv", $this->patientsData, []))
            ->storeAndAttachMediaTo($practice, "patient_report_for_{$dateStr}");
    }

    public function setDate(Carbon $date): GeneratePracticePatientsReport
    {
        $this->date = $date;

        return $this;
    }

    public function setPatientsData(array $patientsData): GeneratePracticePatientsReport
    {
        $this->patientsData = $patientsData;

        return $this;
    }

    public function setPracticeId(int $practiceId): GeneratePracticePatientsReport
    {
        $this->practiceId = $practiceId;

        return $this;
    }
}
