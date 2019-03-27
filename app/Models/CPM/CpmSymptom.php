<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\CPM;

use App\CareItem;
use App\CarePlanTemplate;
use App\Contracts\Serviceable;
use App\Services\CPM\CpmSymptomService;
use CircleLinkHealth\Customer\Entities\User;

/**
 * App\Models\CPM\CpmSymptom.
 *
 * @property int                                                                       $id
 * @property int|null                                                                  $care_item_id
 * @property string                                                                    $name
 * @property \Carbon\Carbon                                                            $created_at
 * @property \Carbon\Carbon                                                            $updated_at
 * @property \App\CareItem                                                             $carePlanItemIdDeprecated
 * @property \App\CarePlanTemplate[]|\Illuminate\Database\Eloquent\Collection          $carePlanTemplates
 * @property \App\Models\CPM\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection $cpmInstructions
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection                      $patient
 * @property \App\Models\CPM\CpmSymptomUser[]|\Illuminate\Database\Eloquent\Collection $users
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom whereCareItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CpmSymptom extends \CircleLinkHealth\Core\Entities\BaseModel implements Serviceable
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
        return $this->belongsToMany(CarePlanTemplate::class, 'care_plan_templates_cpm_symptoms');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsToMany(User::class, 'cpm_symptoms_users', 'patient_id');
    }

    /**
     * Get this Model's Service Class.
     *
     * @return Serviceable
     */
    public function service()
    {
        return new CpmSymptomService();
    }

    public function users()
    {
        return $this->belongsToMany(CpmSymptomUser::class);
    }
}
