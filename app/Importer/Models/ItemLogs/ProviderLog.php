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

    protected $fillable = [
        'location_id',
        'practice_id',
        'billing_provider_id',
        'user_id',
        'medical_record_type',
        'medical_record_id',
        'vendor_id',
        'npi',
        'provider_id',
        'first_name',
        'last_name',
        'organization',
        'street',
        'city',
        'state',
        'zip',
        'cell_phone',
        'home_phone',
        'work_phone',
        'import',
        'invalid',
        'edited',
        'ml_ignore',
    ];

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
