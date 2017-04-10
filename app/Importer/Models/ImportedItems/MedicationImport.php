<?php namespace App\Importer\Models\ImportedItems;

use App\Importer\Models\ItemLogs\MedicationLog;
use App\Models\CPM\CpmMedicationGroup;
use Illuminate\Database\Eloquent\Model;

class MedicationImport extends Model {

    protected $guarded = [];

    public function ccdLog()
    {
        return $this->belongsTo(MedicationLog::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cpmMedicationGroup()
    {
        return $this->belongsTo(CpmMedicationGroup::class, 'medication_group_id');
    }
}
