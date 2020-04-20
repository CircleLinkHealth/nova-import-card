<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\Customer\Entities\User;

/**
 * CircleLinkHealth\SharedModels\Entities\CpmMisc.
 *
 * @property int                                                                                                 $id
 * @property int|null                                                                                            $details_care_item_id
 * @property int|null                                                                                            $care_item_id
 * @property string                                                                                              $name
 * @property \Illuminate\Support\Carbon                                                                          $created_at
 * @property \Illuminate\Support\Carbon                                                                          $updated_at
 * @property \CircleLinkHealth\SharedModels\Entities\CarePlanTemplate[]|\Illuminate\Database\Eloquent\Collection $carePlanTemplates
 * @property \CircleLinkHealth\SharedModels\Entities\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection   $cpmInstructions
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection                 $patient
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection         $revisionHistory
 * @property \CircleLinkHealth\SharedModels\Entities\CpmMiscUser[]|\Illuminate\Database\Eloquent\Collection      $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereCareItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereDetailsCareItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $care_plan_templates_count
 * @property int|null $cpm_instructions_count
 * @property int|null $patient_count
 * @property int|null $revision_history_count
 * @property int|null $users_count
 */
class CpmMisc extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use Instructable;

    const ALLERGIES              = 'Allergies';
    const APPOINTMENTS           = 'Appointments';
    const MEDICATION_LIST        = 'Medication List';
    const OLD_MEDS_LIST          = 'Old Meds List';
    const OTHER                  = 'Other';
    const OTHER_CONDITIONS       = 'Full Conditions List';
    const SOCIAL_SERVICES        = 'Social Services';
    const TRACK_CARE_TRANSITIONS = 'Track Care Transitions';

    protected $fillable = [
        'details_care_item_id',
        'care_item_id',
        'name',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function carePlanTemplates()
    {
        return $this->belongsToMany(CarePlanTemplate::class, 'care_plan_templates_cpm_miscs');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsToMany(User::class, 'cpm_miscs_users', 'patient_id');
    }

    public function users()
    {
        return $this->hasMany(CpmMiscUser::class, 'cpm_misc_id');
    }
}
