<?php

namespace App\Importer\Models\ItemLogs;

use App\Models\CCD\CcdInsurancePolicy;
use App\Traits\BelongsToCcda;
use App\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Model;

class InsuranceLog extends Model
{
    use BelongsToCcda, BelongsToVendor;

    protected $fillable = [
        'medical_record_id',
        'medical_record_type',
        'name',
        'type',
        'policy_id',
        'relation',
        'subscriber',
    ];

    public function importedItem()
    {
        return $this->hasOne(CcdInsurancePolicy::class);
    }
}
