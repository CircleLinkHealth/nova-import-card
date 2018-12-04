<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * App\Family.
 *
 * @property int                                                     $id
 * @property string|null                                             $name
 * @property int|null                                                $created_by
 * @property \Carbon\Carbon|null                                     $created_at
 * @property \Carbon\Carbon|null                                     $updated_at
 * @property \App\Patient[]|\Illuminate\Database\Eloquent\Collection $patients
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Family whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Family whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Family whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Family whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Family whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Family extends \App\BaseModel
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
