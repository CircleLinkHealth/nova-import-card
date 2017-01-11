<?php namespace App\Importer\Models\ItemLogs;

use App\Contracts\Importer\HealthRecord\Section\ItemLog;
use App\Importer\Models\ImportedItems\AllergyImport;
use App\Traits\BelongsToCcda;
use App\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Model;

class AllergyLog extends Model implements ItemLog
{
    use BelongsToCcda, BelongsToVendor;

    protected $table = 'ccd_allergy_logs';

    protected $guarded = [];

    public function importedItem()
    {
        return $this->hasOne(AllergyImport::class);
    }

}
