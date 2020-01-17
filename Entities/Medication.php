<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use App\Importer\Models\ItemLogs\MedicationLog;
use CircleLinkHealth\SharedModels\Entities\CpmMedicationGroup;
use CircleLinkHealth\Customer\Entities\User;

/**
 * CircleLinkHealth\SharedModels\Entities\Medication.
 *
 * @property int                                         $id
 * @property bool|null                                   $active
 * @property int|null                                    $medication_import_id
 * @property int|null                                    $ccda_id
 * @property int                                         $patient_id
 * @property int|null                                    $vendor_id
 * @property int|null                                    $ccd_medication_log_id
 * @property int|null                                    $medication_group_id
 * @property string|null                                 $name
 * @property string|null                                 $sig
 * @property string|null                                 $code
 * @property string|null                                 $code_system
 * @property string|null                                 $code_system_name
 * @property string|null                                 $deleted_at
 * @property \Carbon\Carbon                              $created_at
 * @property \Carbon\Carbon                              $updated_at
 * @property \App\Importer\Models\ItemLogs\MedicationLog $ccdLog
 * @property \CircleLinkHealth\SharedModels\Entities\CpmMedicationGroup          $cpmMedicationGroup
 * @property \CircleLinkHealth\Customer\Entities\User    $patient
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereCcdMedicationLogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereCcdaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereCodeSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereCodeSystemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereMedicationGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereMedicationImportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereSig($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereVendorId($value)
 * @mixin \Eloquent
 * @property \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Revisionable\Entities\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereActive($value)
 * @property int|null $revision_history_count
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
    public function ccdLog()
    {
        return $this->belongsTo(MedicationLog::class);
    }

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
