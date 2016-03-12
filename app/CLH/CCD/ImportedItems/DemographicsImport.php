<?php namespace App\CLH\CCD\ImportedItems;

use App\CLH\CCD\ItemLogger\CcdDemographicsLog;
use Illuminate\Database\Eloquent\Model;

class DemographicsImport extends Model {

    protected $guarded = [];

    public function ccdLog()
    {
        return $this->belongsTo(CcdDemographicsLog::class);
    }

}
