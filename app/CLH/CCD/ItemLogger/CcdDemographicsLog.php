<?php namespace App\CLH\CCD\ItemLogger;

use App\CLH\CCD\Ccda;
use App\CLH\CCD\ImportedItems\DemographicsImport;
use App\CLH\Contracts\CCD\CcdItemLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CcdDemographicsLog extends Model implements CcdItemLog {

    use BelongsToCcda, LogVendorRelationship;

    protected $guarded = [];

    public function importedItem()
    {
        return $this->hasOne(DemographicsImport::class);
    }

}