<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\Ehr;
use CircleLinkHealth\Customer\Entities\User;

/**
 * App\TargetPatient.
 *
 * @property int                                                                            $id
 * @property int|null                                                                       $batch_id
 * @property string|null                                                                    $eligibility_job_id
 * @property int                                                                            $ehr_id
 * @property int|null                                                                       $user_id
 * @property int|null                                                                       $enrollee_id
 * @property int                                                                            $ehr_patient_id
 * @property int                                                                            $ehr_practice_id
 * @property int                                                                            $ehr_department_id
 * @property string|null                                                                    $status
 * @property \Illuminate\Support\Carbon|null                                                $created_at
 * @property \Illuminate\Support\Carbon|null                                                $updated_at
 * @property string                                                                         $description
 * @property \CircleLinkHealth\Customer\Entities\Ehr                                        $ehr
 * @property \App\Enrollee|null                                                             $enrollee
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @property \CircleLinkHealth\Customer\Entities\User|null                                  $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereEhrDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereEhrId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereEhrPatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereEhrPracticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereEligibilityJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereEnrolleeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereUserId($value)
 * @mixin \Eloquent
 */
class TargetPatient extends BaseModel
{
    const STATUS_CONSENTED  = 'consented';
    const STATUS_ELIGIBLE   = 'eligible';
    const STATUS_ENROLLED   = 'enrolled';
    const STATUS_ERROR      = 'error';
    const STATUS_INELIGIBLE = 'ineligible';
    const STATUS_TO_PROCESS = 'to_process';

    protected $guarded = [];

    public function batch()
    {
        return $this->belongsTo(EligibilityBatch::class, 'batch_id');
    }

    public function ehr()
    {
        return $this->belongsTo(Ehr::class, 'ehr_id');
    }

    public function enrollee()
    {
        return $this->belongsTo(Enrollee::class, 'enrollee_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
