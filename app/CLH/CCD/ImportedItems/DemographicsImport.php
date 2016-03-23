<?php namespace App\CLH\CCD\ImportedItems;

use App\CLH\CCD\ItemLogger\CcdDemographicsLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DemographicsImport extends Model {

    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $guarded = [];

    public function ccdLog()
    {
        return $this->belongsTo(CcdDemographicsLog::class);
    }

}
