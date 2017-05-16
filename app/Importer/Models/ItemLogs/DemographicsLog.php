<?php namespace App\Importer\Models\ItemLogs;

use App\Contracts\Importer\MedicalRecord\Section\ItemLog;
use App\Importer\Models\ImportedItems\DemographicsImport;
use App\Traits\Relationships\BelongsToCcda;
use App\Traits\Relationships\BelongsToVendor;
use Illuminate\Database\Eloquent\Model;

class DemographicsLog extends Model implements ItemLog
{

    use BelongsToCcda,
        BelongsToVendor;

    protected $table = 'ccd_demographics_logs';

    protected $fillable = [
        'medical_record_type',
        'medical_record_id',
        'first_name',
        'last_name',
        'dob',
        'gender',
        'mrn_number',
        'street',
        'street2',
        'city',
        'state',
        'zip',
        'cell_phone',
        'home_phone',
        'work_phone',
        'primary_phone',
        'email',
        'language',
        'race',
        'ethnicity',
        'preferred_call_times',
        'preferred_call_days',
    ];

    public function importedItem()
    {
        return $this->hasOne(DemographicsImport::class);
    }

}