<?php namespace App\Importer\Models\ItemLogs;

use App\CLH\CCD\ImportedItems\ProblemImport;
use App\Contracts\Importer\HealthRecord\Section\ItemLog;
use Illuminate\Database\Eloquent\Model;

class ProblemLog extends Model implements ItemLog
{

    use App\Traits\BelongsToCcda, App\Traits\BelongsToVendor;

    protected $guarded = [];

    public function importedItem()
    {
        return $this->hasOne(ProblemImport::class);
    }

}