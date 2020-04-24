<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\Customer\Entities\User;

/**
 * CircleLinkHealth\SharedModels\Entities\Allergy.
 *
 * @property int                                                                                         $id
 * @property int|null                                                                                    $allergy_import_id
 * @property int|null                                                                                    $ccda_id
 * @property int                                                                                         $patient_id
 * @property int|null                                                                                    $vendor_id
 * @property int|null                                                                                    $ccd_allergy_log_id
 * @property string|null                                                                                 $allergen_name
 * @property string|null                                                                                 $deleted_at
 * @property \Illuminate\Support\Carbon                                                                  $created_at
 * @property \Illuminate\Support\Carbon                                                                  $updated_at
 * @property \CircleLinkHealth\Customer\Entities\User                                                    $patient
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Allergy newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Allergy newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Allergy query()
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Allergy whereAllergenName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Allergy whereAllergyImportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Allergy whereCcdAllergyLogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Allergy whereCcdaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Allergy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Allergy whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Allergy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Allergy wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Allergy whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Allergy whereVendorId($value)
 */
class Allergy extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $fillable = [
        'ccda_id',
        'vendor_id',
        'patient_id',
        'ccd_allergy_log_id',
        'allergen_name',
    ];

    protected $table = 'ccd_allergies';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
