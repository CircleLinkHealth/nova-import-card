<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\CPM;

use App\CareItem;
use App\CarePlanTemplate;
use App\Contracts\Serviceable;
use App\Services\CPM\CpmLifestyleService;
use CircleLinkHealth\Customer\Entities\User;

/**
 * App\Models\CPM\CpmLifestyle.
 *
 * @property int                                                                         $id
 * @property int|null                                                                    $care_item_id
 * @property string                                                                      $name
 * @property \Carbon\Carbon                                                              $created_at
 * @property \Carbon\Carbon                                                              $updated_at
 * @property \App\CareItem                                                               $carePlanItemIdDeprecated
 * @property \App\CarePlanTemplate[]|\Illuminate\Database\Eloquent\Collection            $carePlanTemplates
 * @property \App\Models\CPM\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection   $cpmInstructions
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection                        $patient
 * @property \App\Models\CPM\CpmLifestyleUser[]|\Illuminate\Database\Eloquent\Collection $users
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereCareItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CpmLifestyle extends \App\BaseModel implements Serviceable
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
        return $this->belongsToMany(CarePlanTemplate::class, 'care_plan_templates_cpm_lifestyles');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsToMany(User::class, 'cpm_lifestyles_users', 'patient_id');
    }

    /**
     * Get this Model's Service Class.
     *
     * @return Serviceable
     */
    public function service()
    {
        return new CpmLifestyleService();
    }

    public function users()
    {
        return $this->hasMany(CpmLifestyleUser::class);
    }
}
