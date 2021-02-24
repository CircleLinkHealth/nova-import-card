<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\Customer\Entities\User;

/**
 * CircleLinkHealth\SharedModels\Entities\CpmLifestyle.
 *
 * @property int                                                                                                 $id
 * @property int|null                                                                                            $care_item_id
 * @property string                                                                                              $name
 * @property \Carbon\Carbon                                                                                      $created_at
 * @property \Carbon\Carbon                                                                                      $updated_at
 * @property \CircleLinkHealth\SharedModels\Entities\CarePlanTemplate[]|\Illuminate\Database\Eloquent\Collection $carePlanTemplates
 * @property \CircleLinkHealth\SharedModels\Entities\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection   $cpmInstructions
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection                 $patient
 * @property \CircleLinkHealth\SharedModels\Entities\CpmLifestyleUser[]|\Illuminate\Database\Eloquent\Collection $users
 * @method static                                                                                              \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereCareItemId($value)
 * @method static                                                                                              \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereCreatedAt($value)
 * @method static                                                                                              \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereId($value)
 * @method static                                                                                              \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereName($value)
 * @method static                                                                                              \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle newModelQuery()
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle newQuery()
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle query()
 * @property int|null                                                                                    $care_plan_templates_count
 * @property int|null                                                                                    $cpm_instructions_count
 * @property int|null                                                                                    $patient_count
 * @property int|null                                                                                    $revision_history_count
 * @property int|null                                                                                    $users_count
 */
class CpmLifestyle extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use Instructable;

    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function carePlanTemplates()
    {
        return $this->belongsToMany(CarePlanTemplate::class, 'care_plan_templates_cpm_lifestyles');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsToMany(User::class, 'cpm_lifestyles_users', 'patient_id');
    }

    public function users()
    {
        return $this->hasMany(CpmLifestyleUser::class);
    }
}
