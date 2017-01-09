<?php namespace App\Traits;

use App\CLH\CCD\ImportedItems\DemographicsImport;
use App\Importer\Models\ItemLogs\AllergyLog;
use App\Importer\Models\ItemLogs\DemographicsLog;
use App\Importer\Models\ItemLogs\DocumentLog;
use App\Importer\Models\ItemLogs\MedicationLog;
use App\Importer\Models\ItemLogs\ProblemLog;
use App\Importer\Models\ItemLogs\ProviderLog;

/**
 * This trait defines all the CCD Logger relationships.
 * We are putting them all together in this trait so that they can be easily re-used in case.
 *
 * Class HealthRecordItemLoggerRelationships
 * @package App\CLH\CCD\ItemLogger
 */
trait HealthRecordItemLoggerRelationships
{
    public function allergies()
    {
        return $this->hasMany(AllergyLog::class);
    }

    public function demographics()
    {
        return $this->hasOne(DemographicsLog::class);
    }

    public function document()
    {
        return $this->hasOne(DocumentLog::class);
    }

    public function medications()
    {
        return $this->hasMany(MedicationLog::class);
    }

    public function problems()
    {
        return $this->hasMany(ProblemLog::class);
    }

    public function providers()
    {
        return $this->hasMany(ProviderLog::class);
    }

    public function demographicsImports()
    {
        return $this->hasOne(DemographicsImport::class);
    }

}