<?php namespace App\Importer\Models\ItemLogs;

use App\Contracts\Importer\MedicalRecord\Section\ItemLog;
use App\Importer\Models\ImportedItems\MedicationImport;
use App\Traits\BelongsToCcda;
use App\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Model;

class MedicationLog extends Model implements ItemLog
{

    use BelongsToCcda, BelongsToVendor;

    protected $table = 'ccd_medication_logs';

    protected $guarded = [];

    public function importedItem()
    {
        return $this->hasOne(MedicationImport::class);
    }

}