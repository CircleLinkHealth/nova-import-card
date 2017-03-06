<?php namespace App\Importer\Models\ItemLogs;

use App\Contracts\Importer\MedicalRecord\Section\ItemLog;
use App\Traits\Relationships\BelongsToCcda;
use App\Traits\Relationships\BelongsToVendor;
use Illuminate\Database\Eloquent\Model;

class ProviderLog extends Model implements ItemLog
{
    use BelongsToCcda,
        BelongsToVendor;

    protected $table = 'ccd_provider_logs';

    protected $guarded = [];

    public function importedItem()
    {
        return $this->hasOne(ProviderImport::class);
    }

    /**
     * Get all of the owning commentable models.
     */
    public function providerLoggable()
    {
        return $this->morphTo();
    }
}