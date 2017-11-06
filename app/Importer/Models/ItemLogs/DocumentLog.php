<?php namespace App\Importer\Models\ItemLogs;

use App\Contracts\Importer\MedicalRecord\Section\ItemLog;
use App\Traits\Relationships\BelongsToCcda;
use App\Traits\Relationships\BelongsToVendor;
use Illuminate\Database\Eloquent\Model;

class DocumentLog extends \App\BaseModel implements ItemLog
{

    use BelongsToCcda,
        BelongsToVendor;

    protected $table = 'ccd_document_logs';

    protected $guarded = [];
}
