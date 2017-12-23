<?php

namespace App\Importer\Models\ItemLogs;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Importer\Models\ItemLogs\ProblemCodeLog
 *
 * @property int $id
 * @property int|null $ccd_problem_log_id
 * @property string $code_system_name
 * @property string|null $code_system_oid
 * @property string $code
 * @property string|null $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Importer\Models\ItemLogs\ProblemLog|null $problemLog
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereCcdProblemLogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereCodeSystemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereCodeSystemOid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProblemCodeLog extends \App\BaseModel
{
    protected $table = 'ccd_problem_code_logs';

    public $fillable = [
        'ccd_problem_log_id',
        'code_system_name',
        'code_system_oid',
        'code',
        'name',
    ];

    public function problemLog()
    {
        return $this->belongsTo(ProblemLog::class, 'ccd_problem_log_id');
    }
}
