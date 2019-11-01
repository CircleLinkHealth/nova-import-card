<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Models\ImportedItems;

use App\Importer\Models\ItemLogs\AllergyLog;

/**
 * App\Importer\Models\ImportedItems\AllergyImport.
 *
 * @property int                                      $id
 * @property string|null                              $medical_record_type
 * @property int|null                                 $medical_record_id
 * @property int                                      $imported_medical_record_id
 * @property int|null                                 $vendor_id
 * @property int                                      $ccd_allergy_log_id
 * @property string|null                              $allergen_name
 * @property int|null                                 $substitute_id
 * @property string|null                              $deleted_at
 * @property \Carbon\Carbon                           $created_at
 * @property \Carbon\Carbon                           $updated_at
 * @property \App\Importer\Models\ItemLogs\AllergyLog $ccdLog
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereAllergenName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereCcdAllergyLogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereImportedMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereMedicalRecordType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereSubstituteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereVendorId($value)
 * @mixin \Eloquent
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport query()
 * @property int|null $revision_history_count
 */
class AllergyImport extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $guarded = [];

    public function ccdLog()
    {
        return $this->belongsTo(AllergyLog::class);
    }
}
