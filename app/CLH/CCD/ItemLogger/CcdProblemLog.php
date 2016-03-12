<?php namespace App\CLH\CCD\ItemLogger;

use App\CLH\CCD\Ccda;
use App\CLH\CCD\ImportedItems\ProblemImport;
use App\CLH\Contracts\CCD\CcdItemLog;
use Illuminate\Database\Eloquent\Model;

class CcdProblemLog extends Model implements CcdItemLog
{

    use LogCcdaRelationship, LogVendorRelationship;

    protected $guarded = [];

    public function importedItem()
    {
        return $this->hasOne(ProblemImport::class);
    }

}