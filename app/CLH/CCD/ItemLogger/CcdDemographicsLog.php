<?php namespace App\CLH\CCD\ItemLogger;

use App\CLH\CCD\Ccda;
use App\CLH\Contracts\CCD\CcdItemLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CcdDemographicsLog extends Model implements CcdItemLog {

    use LogCcdaRelationship, LogVendorRelationship;

    protected $guarded = [];

}