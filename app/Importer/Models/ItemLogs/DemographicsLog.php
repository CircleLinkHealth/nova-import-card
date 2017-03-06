<?php namespace App\Importer\Models\ItemLogs;

use App\Contracts\Importer\MedicalRecord\Section\ItemLog;
use App\Importer\Models\ImportedItems\DemographicsImport;
use Illuminate\Database\Eloquent\Model;

class DemographicsLog extends Model implements ItemLog
{

    use App\Traits\Relationships\BelongsToCcda, App\Traits\Relationships\BelongsToVendor;

    protected $table = 'ccd_demographics_logs';

    protected $guarded = [];

    public function importedItem()
    {
        return $this->hasOne(DemographicsImport::class);
    }

}