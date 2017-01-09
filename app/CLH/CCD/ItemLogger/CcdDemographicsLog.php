<?php namespace App\CLH\CCD\ItemLogger;

use App\CLH\CCD\ImportedItems\DemographicsImport;
use App\CLH\Contracts\CCD\HealthRecordSectionLog;
use Illuminate\Database\Eloquent\Model;

class CcdDemographicsLog extends Model implements HealthRecordSectionLog
{

    use BelongsToCcda, LogVendorRelationship;

    protected $guarded = [];

    public function importedItem()
    {
        return $this->hasOne(DemographicsImport::class);
    }

}