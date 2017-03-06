<?php namespace App\Importer\Models\ItemLogs;

use App\Contracts\Importer\MedicalRecord\Section\ItemLog;
use App\Importer\Models\ImportedItems\MedicationImport;
use Illuminate\Database\Eloquent\Model;

class MedicationLog extends Model implements ItemLog
{

    use App\Traits\Relationships\BelongsToCcda, App\Traits\Relationships\BelongsToVendor;

    protected $table = 'ccd_medication_logs';

    protected $guarded = [];

    public function importedItem()
    {
        return $this->hasOne(MedicationImport::class);
    }

}