<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\Customer\Entities\User;

/**
 * CircleLinkHealth\SharedModels\Entities\Allergy.
 *
 * @property int                                                $id
 * @property int|null                                           $allergy_import_id
 * @property int|null                                           $ccda_id
 * @property int                                                $patient_id
 * @property int|null                                           $vendor_id
 * @property int|null                                           $ccd_allergy_log_id
 * @property string|null                                        $allergen_name
 * @property string|null                                        $deleted_at
 * @property \Carbon\Carbon                                     $created_at
 * @property \Carbon\Carbon                                     $updated_at
 * @property \CircleLinkHealth\SharedModels\Entities\AllergyLog $ccdLog
 * @property \CircleLinkHealth\Customer\Entities\User           $patient
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereAllergenName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereAllergyImportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereCcdAllergyLogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereCcdaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereVendorId($value)
 * @mixin \Eloquent
 *
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy query()
 *
 * @property int|null $revision_history_count
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ccdLog()
    {
        return $this->belongsTo(AllergyLog::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
