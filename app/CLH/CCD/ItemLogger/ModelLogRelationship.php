<?php

namespace App\CLH\CCD\ItemLogger;
use App\CLH\CCD\ImportedItems\DemographicsImport;

/**
 * This trait defines all the CCD Logger relationships.
 * We are putting them all together in this trait so that they can be easily re-used in case.
 *
 * Class ModelLogRelationship
 * @package App\CLH\CCD\ItemLogger
 */
trait ModelLogRelationship
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
        return $this->hasOne( DemographicsImport::class );
    }

}