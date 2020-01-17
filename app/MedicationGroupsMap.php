<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\SharedModels\Entities\CpmMedicationGroup;

/**
 * App\MedicationGroupsMap.
 *
 * @property int                                                        $id
 * @property string                                                     $keyword
 * @property int                                                        $medication_group_id
 * @property \Carbon\Carbon|null                                        $created_at
 * @property \Carbon\Carbon|null                                        $updated_at
 * @property \CircleLinkHealth\SharedModels\Entities\CpmMedicationGroup $cpmMedicationGroup
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MedicationGroupsMap whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MedicationGroupsMap whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MedicationGroupsMap whereKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MedicationGroupsMap whereMedicationGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MedicationGroupsMap whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Revisionable\Entities\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MedicationGroupsMap newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MedicationGroupsMap newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MedicationGroupsMap query()
 * @property int|null $revision_history_count
 */
class MedicationGroupsMap extends \CircleLinkHealth\Core\Entities\BaseModel
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

    /**
     * Get the medication group to activate.
     *
     * @param $name
     *
     * @return int|null
     */
    public static function getGroup($name)
    {
        $maps = MedicationGroupsMap::all();

        foreach ($maps as $map) {
            if (str_contains(strtolower($name), strtolower($map->keyword))) {
                return $map->medication_group_id;
            }
        }

        return null;
    }
}
