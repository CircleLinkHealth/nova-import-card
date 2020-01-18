<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits\Relationships;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\MedicationLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\DemographicsImport;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\DemographicsLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\DocumentLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProviderLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemLog;
use CircleLinkHealth\SharedModels\Entities\AllergyLog;

/**
 * This trait defines all the CCD Logger relationships.
 * We are putting them all together in this trait so that they can be easily re-used in case.
 *
 * Class MedicalRecordItemLoggerRelationships
 */
trait MedicalRecordItemLoggerRelationships
{
    public function allergies()
    {
        return $this->morphMany(AllergyLog::class, 'providerLoggable', 'medical_record_type', 'medical_record_id');
    }

    public function demographics()
    {
        return $this->morphMany(DemographicsLog::class, 'providerLoggable', 'medical_record_type', 'medical_record_id');
    }

    public function demographicsImports()
    {
        return $this->morphMany(
            DemographicsImport::class,
            'providerLoggable',
            'medical_record_type',
            'medical_record_id'
        );
    }

    public function document()
    {
        return $this->morphMany(DocumentLog::class, 'providerLoggable', 'medical_record_type', 'medical_record_id');
    }

    public function medications()
    {
        return $this->morphMany(MedicationLog::class, 'providerLoggable', 'medical_record_type', 'medical_record_id');
    }

    public function problems()
    {
        return $this->morphMany(ProblemLog::class, 'providerLoggable', 'medical_record_type', 'medical_record_id');
    }

    public function providers()
    {
        return $this->morphMany(ProviderLog::class, 'providerLoggable', 'medical_record_type', 'medical_record_id');
    }
}
