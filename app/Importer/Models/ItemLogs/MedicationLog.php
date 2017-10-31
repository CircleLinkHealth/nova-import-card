<?php namespace App\Importer\Models\ItemLogs;

use App\Contracts\Importer\MedicalRecord\Section\ItemLog;
use App\Importer\Models\ImportedItems\MedicationImport;
use App\Traits\Relationships\BelongsToCcda;
use App\Traits\Relationships\BelongsToVendor;
use Illuminate\Database\Eloquent\Model;

class MedicationLog extends Model implements ItemLog
{

    use BelongsToCcda,
        BelongsToVendor;

    protected $table = 'ccd_medication_logs';

    protected $fillable = [
        'medical_record_type',
        'medical_record_id',
        'vendor_id',
        'reference',
        'reference_title',
        'reference_sig',
        'start',
        'end',
        'status',
        'text',
        'product_name',
        'product_code',
        'product_code_system',
        'product_text',
        'translation_name',
        'translation_code',
        'translation_code_system',
        'translation_code_system_name',
        'import',
        'invalid',
        'edited',
    ];

    public function importedItem()
    {
        return $this->hasOne(MedicationImport::class);
    }
}
