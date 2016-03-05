<?php namespace App\CLH\CCD\ImportedItemsLogger;

use App\CLH\CCD\Ccda;
use App\CLH\Contracts\CCD\CcdItemLog;
use Illuminate\Database\Eloquent\Model;

class CcdProviderLog extends Model implements CcdItemLog
{

    use LogCcdaRelationship, LogVendorRelationship;

    protected $guarded = [];

}