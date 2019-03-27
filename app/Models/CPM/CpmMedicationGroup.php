<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\CPM;

use App\CareItem;
use App\CarePlanTemplate;
use App\Contracts\Serviceable;
use App\Models\CCD\Medication;
use App\Services\CPM\CpmMedicationGroupService;
use CircleLinkHealth\Customer\Entities\User;

/**
 * App\Models\CPM\CpmMedicationGroup.
 *
 * @property int                                                                       $id
 * @property int|null                                                                  $care_item_id
 * @property string                                                                    $name
 * @property \Carbon\Carbon                                                            $created_at
 * @property \Carbon\Carbon                                                            $updated_at
 * @property \App\CareItem                                                             $carePlanItemIdDeprecated
 * @property \App\CarePlanTemplate[]|\Illuminate\Database\Eloquent\Collection          $carePlanTemplates
 * @property \App\Models\CPM\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection $cpmInstructions
 * @property \App\Models\CCD\Medication[]|\Illuminate\Database\Eloquent\Collection     $medications
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection                      $patient
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMedicationGroup whereCareItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMedicationGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMedicationGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMedicationGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMedicationGroup whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CpmMedicationGroup extends \CircleLinkHealth\Core\Entities\BaseModel implements Serviceable
{
    use Instructable;

    protected $guarded = [];

    public function carePlanItemIdDeprecated()
    {
        return $this->belongsTo(CareItem::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function carePlanTemplates()
    {
        return $this->belongsToMany(CarePlanTemplate::class, 'care_plan_templates_cpm_medication_groups');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function medications()
    {
        return $this->hasMany(Medication::class, 'medication_group_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsToMany(User::class, 'cpm_medication_groups_users', 'patient_id');
    }

    /**
     * Get this Model's Service Class.
     *
     * @return Serviceable
     */
    public function service()
    {
        return new CpmMedicationGroupService();
    }
}
