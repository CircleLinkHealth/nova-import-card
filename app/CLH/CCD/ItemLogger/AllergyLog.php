<?php namespace App\CLH\CCD\ItemLogger;

use App\CLH\CCD\ImportedItems\AllergyImport;
use App\Contracts\Importer\HealthRecord\Section\ItemLog;
use Illuminate\Database\Eloquent\Model;

class AllergyLog extends Model implements ItemLog
{
    use BelongsToCcda, LogVendorRelationship;

    protected $guarded = [];

    public function importedItem()
    {
        return $this->hasOne(AllergyImport::class);
    }

}
