<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CarePlanModels\Entities;

use CircleLinkHealth\CarePlanModels\Entities\MedicationLog;
use CircleLinkHealth\CarePlanModels\Entities\CpmMedicationGroup;
use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * CircleLinkHealth\CarePlanModels\Entities\MedicationImport.
 *
 * @property int                                         $id
 * @property string|null                                 $medical_record_type
 * @property int|null                                    $medical_record_id
 * @property int                                         $imported_medical_record_id
 * @property int|null                                    $vendor_id
 * @property int                                         $ccd_medication_log_id
 * @property int|null                                    $medication_group_id
 * @property string|null                                 $name
 * @property string|null                                 $sig
 * @property string|null                                 $code
 * @property string|null                                 $code_system
 * @property string|null                                 $code_system_name
 * @property int|null                                    $substitute_id
 * @property string|null                                 $deleted_at
 * @property \Carbon\Carbon                              $created_at
 * @property \Carbon\Carbon                              $updated_at
 * @property \CircleLinkHealth\CarePlanModels\Entities\MedicationLog $ccdLog
 * @property \CircleLinkHealth\CarePlanModels\Entities\CpmMedicationGroup|null     $cpmMedicationGroup
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereCcdMedicationLogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereCodeSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereCodeSystemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereImportedMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereMedicalRecordType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereMedicationGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereSig($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereSubstituteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereVendorId($value)
 * @mixin \Eloquent
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport query()
 * @property int|null $revision_history_count
 */
class MedicationImport extends BaseModel
{
    protected $guarded = [];

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
}
