<?php namespace App\CLH\CCD\ItemLogger;

use App\CLH\CCD\Ccda;
use App\CLH\CCD\ImportedItems\MedicationImport;
use App\CLH\Contracts\CCD\CcdItemLog;
use Illuminate\Database\Eloquent\Model;

class CcdMedicationLog extends Model implements CcdItemLog {

    use LogCcdaRelationship, LogVendorRelationship;

    protected $guarded = [];

    public function importedItem()
    {
        return $this->hasOne(MedicationImport::class);
    }

}