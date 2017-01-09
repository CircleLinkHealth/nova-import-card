<?php namespace App\Importer\Models\ItemLogs;

use App\Contracts\Importer\HealthRecord\Section\ItemLog;
use Illuminate\Database\Eloquent\Model;

class DocumentLog extends Model implements ItemLog
{

    use App\Traits\BelongsToCcda, App\Traits\BelongsToVendor;

	protected $guarded = [];

}
