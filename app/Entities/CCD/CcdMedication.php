<?php namespace App\Entities\CCD;

use App\CLH\CCD\ItemLogger\CcdMedicationLog;
use App\Entities\CPM\CpmMedicationGroup;
use App\User;
use Illuminate\Database\Eloquent\Model;

class CcdMedication extends Model {

    protected $guarded = [];

    protected $table = 'ccd_medications';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ccdLog()
    {
        return $this->belongsTo(CcdMedicationLog::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function medicationGroup()
    {
        return $this->belongsTo(CpmMedicationGroup::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsTo(User::class);
    }
}
