<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Scopes\MedicalRecordIdAndTypeTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CircleLinkHealth\SharedModels\Entities\CcdInsurancePolicy.
 *
 * @property int                                           $id
 * @property int|null                                      $medical_record_id
 * @property string|null                                   $medical_record_type
 * @property int|null                                      $patient_id
 * @property string                                        $name
 * @property string|null                                   $type
 * @property string|null                                   $policy_id
 * @property string|null                                   $relation
 * @property string|null                                   $subscriber
 * @property int                                           $approved
 * @property \Carbon\Carbon                                $created_at
 * @property \Carbon\Carbon                                $updated_at
 * @property string|null                                   $deleted_at
 * @property \CircleLinkHealth\Customer\Entities\User|null $patient
 *
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CCD\CcdInsurancePolicy onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereMedicalRecordType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy wherePolicyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereSubscriber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy withMedicalRecord($id, $type = 'CircleLinkHealth\SharedModels\Entities\Ccda')
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CCD\CcdInsurancePolicy withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CCD\CcdInsurancePolicy withoutTrashed()
 * @mixin \Eloquent
 *
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy query()
 *
 * @property int|null $revision_history_count
 */
class CcdInsurancePolicy extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use MedicalRecordIdAndTypeTrait;
    use SoftDeletes;

    protected $attributes = [
        'approved' => false,
    ];

    protected $fillable = [
        'medical_record_id',
        'medical_record_type',
        'patient_id',
        'name', //required
        'type',
        'policy_id', //required
        'relation',
        'subscriber',
        'approved',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
