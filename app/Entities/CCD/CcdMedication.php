<?php namespace App\Entities\CCD;

use App\CLH\CCD\ItemLogger\CcdMedicationLog;
use Illuminate\Database\Eloquent\Model;

class CcdMedication extends Model {

    protected $guarded = [];

    protected $table = 'ccd_medications';

    public function ccdLog()
    {
        return $this->belongsTo(CcdMedicationLog::class);
    }

}
