<?php namespace App\CLH\CCD\ImportedItems;

use App\CLH\CCD\ItemLogger\BelongsToCcda;
use App\CLH\CCD\ItemLogger\CcdDemographicsLog;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DemographicsImport extends Model {

    use BelongsToCcda, SoftDeletes;

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

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

}
