<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\Customer\Entities\User;

/**
 * CircleLinkHealth\SharedModels\Entities\CpmSymptom.
 *
 * @property int                                                                                                 $id
 * @property int|null                                                                                            $care_item_id
 * @property string                                                                                              $name
 * @property \Carbon\Carbon                                                                                      $created_at
 * @property \Carbon\Carbon                                                                                      $updated_at
 * @property \CircleLinkHealth\SharedModels\Entities\CarePlanTemplate[]|\Illuminate\Database\Eloquent\Collection $carePlanTemplates
 * @property \CircleLinkHealth\SharedModels\Entities\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection   $cpmInstructions
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection                 $patient
 * @property \CircleLinkHealth\SharedModels\Entities\CpmSymptomUser[]|\Illuminate\Database\Eloquent\Collection   $users
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom whereCareItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom whereUpdatedAt($value)
 * @mixin \Eloquent
 *
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom query()
 *
 * @property int|null $care_plan_templates_count
 * @property int|null $cpm_instructions_count
 * @property int|null $patient_count
 * @property int|null $revision_history_count
 * @property int|null $users_count
 */
class CpmSymptom extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use Instructable;

    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function carePlanTemplates()
    {
        return $this->belongsToMany(CarePlanTemplate::class, 'care_plan_templates_cpm_symptoms');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsToMany(User::class, 'cpm_symptoms_users', 'patient_id');
    }

    public function users()
    {
        return $this->belongsToMany(CpmSymptomUser::class);
    }
}
