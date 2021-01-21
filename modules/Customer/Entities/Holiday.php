<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

/**
 * CircleLinkHealth\Customer\Entities\Holiday.
 *
 * @property int                                       $id
 * @property int                                       $nurse_info_id
 * @property \Carbon\Carbon                            $date
 * @property \Carbon\Carbon|null                       $created_at
 * @property \Carbon\Carbon|null                       $updated_at
 * @property \CircleLinkHealth\Customer\Entities\Nurse $nurse
 * @method   static                                    \Illuminate\Database\Eloquent\Builder|\App\Models\Holiday whereCreatedAt($value)
 * @method   static                                    \Illuminate\Database\Eloquent\Builder|\App\Models\Holiday whereDate($value)
 * @method   static                                    \Illuminate\Database\Eloquent\Builder|\App\Models\Holiday whereId($value)
 * @method   static                                    \Illuminate\Database\Eloquent\Builder|\App\Models\Holiday whereNurseInfoId($value)
 * @method   static                                    \Illuminate\Database\Eloquent\Builder|\App\Models\Holiday whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Holiday newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Holiday newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Holiday query()
 * @property int|null                                                                                    $revision_history_count
 */
class Holiday extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $dates = [
        'date',
    ];

    protected $fillable = [
        'date',
        'nurse_info_id',
    ];

    public function nurse()
    {
        return $this->belongsTo(Nurse::class, 'nurse_info_id', 'id');
    }
}
