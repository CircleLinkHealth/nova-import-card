<?php namespace App\CLH\CCD\ItemLogger;

use App\Contracts\Importer\HealthRecord\Section\ItemLog;
use Illuminate\Database\Eloquent\Model;

class DocumentLog extends Model implements ItemLog
{

    use BelongsToCcda, LogVendorRelationship;

	protected $guarded = [];

}
