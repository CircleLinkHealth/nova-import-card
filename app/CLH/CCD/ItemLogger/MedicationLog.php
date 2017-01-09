<?php namespace App\CLH\CCD\ItemLogger;

use App\CLH\CCD\ImportedItems\MedicationImport;
use App\Contracts\Importer\HealthRecord\Section\ItemLog;
use Illuminate\Database\Eloquent\Model;

class MedicationLog extends Model implements ItemLog
{

    use BelongsToCcda, LogVendorRelationship;

    protected $guarded = [];

    public function importedItem()
    {
        return $this->hasOne(MedicationImport::class);
    }

}