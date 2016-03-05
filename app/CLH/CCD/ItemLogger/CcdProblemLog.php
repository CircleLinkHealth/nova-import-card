<?php namespace App\CLH\CCD\ItemLogger;

use App\CLH\CCD\Ccda;
use App\CLH\Contracts\CCD\CcdItemLog;
use Illuminate\Database\Eloquent\Model;

class CcdProblemLog extends Model implements CcdItemLog
{

    use LogCcdaRelationship, LogVendorRelationship;

    protected $guarded = [];

}