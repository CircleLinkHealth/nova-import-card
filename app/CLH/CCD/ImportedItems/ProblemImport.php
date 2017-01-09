<?php namespace App\CLH\CCD\ImportedItems;

use App\CLH\CCD\ItemLogger\ProblemLog;
use Illuminate\Database\Eloquent\Model;

class ProblemImport extends Model {

    protected $guarded = [];

    public function ccdLog()
    {
        return $this->belongsTo(ProblemLog::class);
    }

}
