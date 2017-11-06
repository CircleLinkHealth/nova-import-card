<?php namespace App\Models\CCD;

use App\Importer\Models\ItemLogs\MedicationLog;
use App\Models\CPM\CpmMedicationGroup;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Medication extends \App\BaseModel
{

    protected $fillable = [
        'ccda_id',
        'vendor_id',
        'ccd_medication_log_id',
        'medication_group_id',
        'patient_id',
        'name',
        'sig',
        'code',
        'code_system',
        'code_system_name',
    ];

    protected $table = 'ccd_medications';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ccdLog()
    {
        return $this->belongsTo(MedicationLog::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cpmMedicationGroup()
    {
        return $this->belongsTo(CpmMedicationGroup::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
