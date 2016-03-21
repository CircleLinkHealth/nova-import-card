<?php

namespace App\CLH\CCD\ItemLogger;

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
        return $this->hasMany( CcdAllergyLog::class );
    }

    public function demographics()
    {
        return $this->hasOne( CcdDemographicsLog::class );
    }

    public function document()
    {
        return $this->hasOne( CcdDocumentLog::class );
    }

    public function medications()
    {
        return $this->hasMany( CcdMedicationLog::class );
    }

    public function problems()
    {
        return $this->hasMany( CcdProblemLog::class );
    }

    public function providers()
    {
        return $this->hasMany( CcdProviderLog::class );
    }

}