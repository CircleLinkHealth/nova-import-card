<?php namespace App\Importer\Models\ItemLogs;

use App\Contracts\Importer\MedicalRecord\Section\ItemLog;
use App\Importer\Models\ImportedItems\ProblemImport;
use App\Traits\Relationships\BelongsToCcda;
use App\Traits\Relationships\BelongsToVendor;
use Illuminate\Database\Eloquent\Model;

class ProblemLog extends Model implements ItemLog
{

    use BelongsToCcda,
        BelongsToVendor;

    protected $table = 'ccd_problem_logs';

    protected $fillable = [
        'medical_record_type',
        'medical_record_id',
        'vendor_id',
        'reference',
        'reference_title',
        'start',
        'end',
        'status',
        'name',
        'code',
        'code_system',
        'code_system_name',
        'translation_name',
        'translation_code',
        'translation_code_system',
        'translation_code_system_name',
        'import',
        'invalid',
        'edited',
        'cpm_problem_id',
    ];

    public function importedItem()
    {
        return $this->hasOne(ProblemImport::class);
    }

    public function codesLogs() {
        return $this->hasMany(ProblemCodeLog::class, 'ccd_problem_log_id');
    }
}