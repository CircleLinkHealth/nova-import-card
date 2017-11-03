<?php

namespace App;

use App\Models\CPM\CpmMedicationGroup;
use Illuminate\Database\Eloquent\Model;

class MedicationGroupsMap extends \App\BaseModel
{
    protected $fillable = [
        'keyword',
        'medication_group_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cpmMedicationGroup()
    {
        return $this->belongsTo(CpmMedicationGroup::class, 'medication_group_id');
    }
}
