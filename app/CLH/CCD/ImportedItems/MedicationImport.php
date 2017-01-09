<?php namespace App\CLH\CCD\ImportedItems;

use App\Importer\Models\ItemLogs\MedicationLog;
use Illuminate\Database\Eloquent\Model;

class MedicationImport extends Model {

    protected $guarded = [];

    public function ccdLog()
    {
        return $this->belongsTo(MedicationLog::class);
    }

}
