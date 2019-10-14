<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * CircleLinkHealth\Customer\Entities\PatientNurse
 *
 * @property int temporary_nurse_user_id
 * @property Carbon temporary_from
 * @property Carbon temporary_to
 * @property User nurse
 * @property int $id
 * @property int $patient_user_id
 * @property int|null $nurse_user_id
 * @property int|null $temporary_nurse_user_id
 * @property \Illuminate\Support\Carbon|null $temporary_from
 * @property \Illuminate\Support\Carbon|null $temporary_to
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \CircleLinkHealth\Customer\Entities\User|null $nurse
 * @property-read \CircleLinkHealth\Customer\Entities\User $patient
 * @property-read \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @property-read int|null $revision_history_count
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse query()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse whereNurseUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse wherePatientUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse whereTemporaryFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse whereTemporaryNurseUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse whereTemporaryTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientNurse whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PatientNurse extends BaseModel
{
    protected $dates = [
        'temporary_from',
        'temporary_to',
    ];

    protected $fillable = [
        'patient_user_id',
        'nurse_user_id',
        'temporary_nurse_user_id',
        'temporary_from',
        'temporary_to',
    ];

    protected $table = 'patients_nurses';

    public function nurse()
    {
        $now = Carbon::now();
        if ($this->temporary_nurse_user_id && $now->isBetween($this->temporary_from, $this->temporary_to)) {
            $record = $this->belongsTo(User::class, 'temporary_nurse_user_id', 'id')
                        ->whereHas('nurseInfo', function ($q) {
                            $q->where('status', 'active');
                        });

            if ($record->exists()) {
                return $record;
            }
        }

        return $this->belongsTo(User::class, 'nurse_user_id', 'id')
                    ->whereHas('nurseInfo', function ($q) {
                        $q->where('status', 'active');
                    });
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_user_id', 'id');
    }
}
