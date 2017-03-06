<?php namespace App\Importer\Models\ItemLogs;

use App\Contracts\Importer\MedicalRecord\Section\ItemLog;
use Illuminate\Database\Eloquent\Model;

class DocumentLog extends Model implements ItemLog
{

    use App\Traits\Relationships\BelongsToCcda, App\Traits\Relationships\BelongsToVendor;

    protected $table = 'ccd_document_logs';

	protected $guarded = [];

}
