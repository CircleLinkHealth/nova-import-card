<?php namespace App\CLH\CCD\ItemLogger;

use App\Models\CCD\Ccda;
use App\CLH\Contracts\CCD\CcdItemLog;
use Illuminate\Database\Eloquent\Model;

class CcdDocumentLog extends Model implements CcdItemLog {

    use BelongsToCcda, LogVendorRelationship;

	protected $guarded = [];

}
