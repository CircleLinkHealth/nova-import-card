<?php namespace App\App\Ccd;

use App\CLH\CCD\ItemLogger\CcdProblemLog;
use Illuminate\Database\Eloquent\Model;

class CcdProblem extends Model {

    protected $guarded = [];

    protected $table = 'ccd_problems';

    public function ccdLog()
    {
        return $this->belongsTo(CcdProblemLog::class);
    }

}
