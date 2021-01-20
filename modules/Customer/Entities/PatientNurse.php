<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * CircleLinkHealth\Customer\Entities\PatientNurse.
 *
 * @property int                                                                                         $id
 * @property int                                                                                         $patient_user_id
 * @property int|null                                                                                    $nurse_user_id
 * @property int|null                                                                                    $temporary_nurse_user_id
 * @property \Illuminate\Support\Carbon|null                                                             $temporary_from
 * @property \Illuminate\Support\Carbon|null                                                             $temporary_to
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property \CircleLinkHealth\Customer\Entities\User|null                                               $nurse
 * @property \CircleLinkHealth\Customer\Entities\User                                                    $patient
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse
 *     newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse query()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse
 *     whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse
 *     whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse
 *     whereNurseUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse
 *     wherePatientUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse
 *     whereTemporaryFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse
 *     whereTemporaryNurseUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse
 *     whereTemporaryTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse
 *     whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Customer\Entities\User|null $permanentNurse
 * @property \CircleLinkHealth\Customer\Entities\User|null $temporaryNurse
 */
class PatientNurse extends BaseModel
{
    protected $fillable = [
        'patient_user_id',
        'nurse_user_id',
    ];

    protected $table = 'patients_nurses';

    public function nurse()
    {
        return $this->permanentNurse();
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_user_id', 'id');
    }

    public function permanentNurse()
    {
        return $this->belongsTo(User::class, 'nurse_user_id', 'id')
            ->whereHas('nurseInfo', function ($q) {
                $q->where('status', 'active');
            });
    }
}
