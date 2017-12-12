<?php

namespace App\Importer\Models\ItemLogs;

use Illuminate\Database\Eloquent\Model;

class ProblemCodeLog extends Model
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
