<?php namespace App\CLH\CCD\ItemLogger;

use App\CLH\Contracts\CCD\HealthRecordSectionLog;
use Illuminate\Database\Eloquent\Model;

class CcdDocumentLog extends Model implements HealthRecordSectionLog
{

    use BelongsToCcda, LogVendorRelationship;

	protected $guarded = [];

}
