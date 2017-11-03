<?php namespace App\Importer\Models\ImportedItems;

use App\Importer\Models\ItemLogs\ProblemLog;
use Illuminate\Database\Eloquent\Model;

class ProblemImport extends \App\BaseModel
{

    protected $guarded = [];

    public function ccdLog()
    {
        return $this->belongsTo(ProblemLog::class, 'ccd_problem_log_id');
    }
}
