<?php namespace App\CLH\CCD\ItemLogger;

use App\CLH\CCD\ImportedItems\DemographicsImport;
use App\Contracts\Importer\HealthRecord\Section\ItemLog;
use Illuminate\Database\Eloquent\Model;

class DemographicsLog extends Model implements ItemLog
{

    use BelongsToCcda, LogVendorRelationship;

    protected $guarded = [];

    public function importedItem()
    {
        return $this->hasOne(DemographicsImport::class);
    }

}