<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * @property int temporary_nurse_user_id
 * @property Carbon temporary_from
 * @property Carbon temporary_to
 * @property User nurse
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
