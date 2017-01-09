<?php namespace App\CLH\CCD\ItemLogger;

use App\CLH\CCD\ImportedItems\MedicationImport;
use App\CLH\Contracts\CCD\HealthRecordSectionLog;
use Illuminate\Database\Eloquent\Model;

class CcdMedicationLog extends Model implements HealthRecordSectionLog
{

    use BelongsToCcda, LogVendorRelationship;

    protected $guarded = [];

    public function importedItem()
    {
        return $this->hasOne(MedicationImport::class);
    }

}