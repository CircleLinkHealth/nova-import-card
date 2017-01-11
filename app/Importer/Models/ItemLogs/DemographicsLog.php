<?php namespace App\Importer\Models\ItemLogs;

use App\Contracts\Importer\HealthRecord\Section\ItemLog;
use App\Importer\Models\ImportedItems\DemographicsImport;
use App\Traits\BelongsToCcda;
use App\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Model;

class DemographicsLog extends Model implements ItemLog
{

    use BelongsToCcda, BelongsToVendor;

    protected $table = 'ccd_demographics_logs';

    protected $guarded = [];

    public function importedItem()
    {
        return $this->hasOne(DemographicsImport::class);
    }

}