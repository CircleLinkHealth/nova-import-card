<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use CircleLinkHealth\Customer\Entities\Patient;

/**
 * CircleLinkHealth\Customer\Entities\Family.
 *
 * @property int                                                     $id
 * @property string|null                                             $name
 * @property int|null                                                $created_by
 * @property \Carbon\Carbon|null                                     $created_at
 * @property \Carbon\Carbon|null                                     $updated_at
 * @property \CircleLinkHealth\Customer\Entities\Patient[]|\Illuminate\Database\Eloquent\Collection $patients
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Family whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Family whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Family whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Family whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Family whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Family extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $fillable = ['*'];

    protected $table = 'families';

    public function patients()
    {
        return $this->hasMany(Patient::class);
    }

//    public function getClosestCallDateForFamily(){
//
//        return $this->patients()->users()->inboundCalls()->whereStatus('scheduled')->first();
//
//
//    }
}
