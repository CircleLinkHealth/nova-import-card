<?php namespace App\CLH\CCD\ImportedItems;

use App\Importer\Models\ItemLogs\ProblemLog;
use Illuminate\Database\Eloquent\Model;

class ProblemImport extends Model {

    protected $guarded = [];

    public function ccdLog()
    {
        return $this->belongsTo(ProblemLog::class);
    }

}
