<?php namespace App\CLH\CCD\ItemLogger;

use App\CLH\CCD\ImportedItems\ProviderImport;
use App\CLH\Contracts\CCD\HealthRecordSectionLog;
use Illuminate\Database\Eloquent\Model;

class CcdProviderLog extends Model implements HealthRecordSectionLog
{

    use BelongsToCcda, LogVendorRelationship;

    protected $guarded = [];

    public function importedItem()
    {
        return $this->hasOne(ProviderImport::class);
    }

}