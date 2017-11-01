<?php namespace App\Importer\Models\ImportedItems;

use App\Importer\Models\ItemLogs\DemographicsLog;
use App\Traits\Relationships\BelongsToCcda;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class DemographicsImport extends Model implements Transformable
{
    use BelongsToCcda,
        TransformableTrait;

    protected $fillable = [
        'medical_record_type',
        'medical_record_id',
        'imported_medical_record_id',
        'vendor_id',
        'program_id',
        'provider_id',
        'location_id',
        'first_name',
        'last_name',
        'dob',
        'gender',
        'mrn_number',
        'street',
        'city',
        'state',
        'zip',
        'primary_phone',
        'cell_phone',
        'home_phone',
        'work_phone',
        'email',
        'preferred_contact_timezone',
        'consent_date',
        'preferred_contact_language',
        'study_phone_number',
        'substitute_id',
        'preferred_call_times',
        'preferred_call_days',
    ];

    public function ccdLog()
    {
        return $this->belongsTo(DemographicsLog::class);
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
