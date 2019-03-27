<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Models\ImportedItems;

use App\Importer\Models\ItemLogs\ProblemLog;

/**
 * App\Importer\Models\ImportedItems\ProblemImport.
 *
 * @property int                                      $id
 * @property string|null                              $medical_record_type
 * @property int|null                                 $medical_record_id
 * @property int                                      $imported_medical_record_id
 * @property int                                      $ccd_problem_log_id
 * @property string|null                              $name
 * @property string|null                              $code
 * @property string|null                              $code_system
 * @property string|null                              $code_system_name
 * @property int                                      $activate
 * @property int|null                                 $cpm_problem_id
 * @property int|null                                 $substitute_id
 * @property string|null                              $deleted_at
 * @property \Carbon\Carbon                           $created_at
 * @property \Carbon\Carbon                           $updated_at
 * @property \App\Importer\Models\ItemLogs\ProblemLog $ccdLog
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereActivate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereCcdProblemLogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereCodeSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereCodeSystemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereCpmProblemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereImportedMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereMedicalRecordType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereSubstituteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProblemImport extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $guarded = [];

    public function ccdLog()
    {
        return $this->belongsTo(ProblemLog::class, 'ccd_problem_log_id');
    }
}
