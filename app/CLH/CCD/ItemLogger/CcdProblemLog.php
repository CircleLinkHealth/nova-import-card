<?php namespace App\CLH\CCD\ItemLogger;

use App\CLH\CCD\ImportedItems\ProblemImport;
use App\CLH\Contracts\CCD\HealthRecordSectionLog;
use Illuminate\Database\Eloquent\Model;

class CcdProblemLog extends Model implements HealthRecordSectionLog
{

    use BelongsToCcda, LogVendorRelationship;

    protected $guarded = [];

    public function importedItem()
    {
        return $this->hasOne(ProblemImport::class);
    }

}