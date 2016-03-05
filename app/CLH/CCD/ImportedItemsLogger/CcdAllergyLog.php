<?php namespace App\CLH\CCD\ImportedItemsLogger;

use App\CLH\CCD\Ccda;
use App\CLH\CCD\ImportedItemsLogger\LogCcdaRelationship;
use App\CLH\CCD\ImportedItemsLogger\LogVendorRelationship;
use App\CLH\Contracts\CCD\CcdItemLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CcdAllergyLog extends Model implements CcdItemLog
{
    use LogCcdaRelationship, LogVendorRelationship;

    protected $guarded = [];

}
