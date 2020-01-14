<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemLog;

/**
 * CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemCodeLog.
 *
 * @property int                                           $id
 * @property int|null                                      $ccd_problem_log_id
 * @property string                                        $code_system_name
 * @property string|null                                   $code_system_oid
 * @property string                                        $code
 * @property string|null                                   $name
 * @property \Carbon\Carbon|null                           $created_at
 * @property \Carbon\Carbon|null                           $updated_at
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemLog|null $problemLog
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereCcdProblemLogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereCodeSystemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereCodeSystemOid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null                                                                       $problem_code_system_id
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereProblemCodeSystemId($value)
 * @property int|null $revision_history_count
 */
class ProblemCodeLog extends BaseModel
{
    public $fillable = [
        'problem_code_system_id',
        'ccd_problem_log_id',
        'code_system_name',
        'code_system_oid',
        'code',
        'name',
    ];
    protected $table = 'ccd_problem_code_logs';

    public function problemLog()
    {
        return $this->belongsTo(ProblemLog::class, 'ccd_problem_log_id');
    }
}
