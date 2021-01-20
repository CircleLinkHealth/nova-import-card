<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\Customer\Entities\User;

/**
 * CircleLinkHealth\SharedModels\Entities\Medication.
 *
 * @property int                                                                                         $id
 * @property int|null                                                                                    $medication_import_id
 * @property int|null                                                                                    $ccda_id
 * @property int                                                                                         $patient_id
 * @property int|null                                                                                    $vendor_id
 * @property int|null                                                                                    $ccd_medication_log_id
 * @property int|null                                                                                    $medication_group_id
 * @property string|null                                                                                 $name
 * @property string|null                                                                                 $sig
 * @property string|null                                                                                 $code
 * @property string|null                                                                                 $code_system
 * @property string|null                                                                                 $code_system_name
 * @property string|null                                                                                 $deleted_at
 * @property \Illuminate\Support\Carbon                                                                  $created_at
 * @property \Illuminate\Support\Carbon                                                                  $updated_at
 * @property int|null                                                                                    $active
 * @property \CircleLinkHealth\SharedModels\Entities\CpmMedicationGroup|null                             $cpmMedicationGroup
 * @property \CircleLinkHealth\Customer\Entities\User                                                    $patient
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Medication newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Medication newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Medication query()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Medication whereActive($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Medication whereCcdMedicationLogId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Medication whereCcdaId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Medication whereCode($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Medication whereCodeSystem($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Medication whereCodeSystemName($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Medication whereCreatedAt($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Medication whereDeletedAt($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Medication whereId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Medication whereMedicationGroupId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Medication whereMedicationImportId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Medication whereName($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Medication wherePatientId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Medication whereSig($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Medication whereUpdatedAt($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Medication whereVendorId($value)
 * @mixin \Eloquent
 */
class Medication extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $fillable = [
        'active',
        'ccda_id',
        'vendor_id',
        'ccd_medication_log_id',
        'medication_group_id',
        'patient_id',
        'name',
        'sig',
        'code',
        'code_system',
        'code_system_name',
    ];

    protected $table = 'ccd_medications';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cpmMedicationGroup()
    {
        return $this->belongsTo(CpmMedicationGroup::class, 'medication_group_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
