<?php namespace App\CLH\CCD\ItemLogger;

use App\CLH\CCD\ImportedItems\ProblemImport;
use App\Contracts\Importer\HealthRecord\Section\ItemLog;
use Illuminate\Database\Eloquent\Model;

class ProblemLog extends Model implements ItemLog
{

    use BelongsToCcda, LogVendorRelationship;

    protected $guarded = [];

    public function importedItem()
    {
        return $this->hasOne(ProblemImport::class);
    }

}