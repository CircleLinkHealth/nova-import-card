<?php namespace App\Importer\Models\ItemLogs;

use App\Contracts\Importer\MedicalRecord\Section\ItemLog;
use App\Importer\Models\ImportedItems\ProblemImport;
use App\Traits\BelongsToCcda;
use App\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Model;

class ProblemLog extends Model implements ItemLog
{

    use BelongsToCcda, BelongsToVendor;

    protected $table = 'ccd_problem_logs';

    protected $guarded = [];

    public function importedItem()
    {
        return $this->hasOne(ProblemImport::class);
    }

}