<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CPM;

use App\User;
use Spatie\MediaLibrary\Models\Media;

class PatientReportCreatedEvent extends AwvToCpmRedisEvent
{
    protected $channel = 'awv-patient-report-created';
    protected $patient;

    public function __construct(User $patient)
    {
        $this->patient = $patient;
    }

    public function publishReportCreated(Media $report)
    {
        $this->publish([
            'patient_id'      => $this->patient->id,
            'report_media_id' => $report->id,
        ]);
    }
}
