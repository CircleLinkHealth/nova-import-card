<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use App\Models\CPM\CpmMedicationGroup;

/**
 * App\MedicationGroupsMap.
 *
 * @property int                                $id
 * @property string                             $keyword
 * @property int                                $medication_group_id
 * @property \Carbon\Carbon|null                $created_at
 * @property \Carbon\Carbon|null                $updated_at
 * @property \App\Models\CPM\CpmMedicationGroup $cpmMedicationGroup
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MedicationGroupsMap whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MedicationGroupsMap whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MedicationGroupsMap whereKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MedicationGroupsMap whereMedicationGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MedicationGroupsMap whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
