<?php

namespace App\Importer\Models\ItemLogs;

use App\Models\CCD\CcdInsurancePolicy;
use App\Traits\Relationships\BelongsToCcda;
use App\Traits\Relationships\BelongsToVendor;
use Illuminate\Database\Eloquent\Model;

class InsuranceLog extends \App\BaseModel
{
    use BelongsToCcda,
        BelongsToVendor;

    protected $fillable = [
        'medical_record_id',
        'medical_record_type',
        'name',
        'type',
        'policy_id',
        'relation',
        'subscriber',
        'import'
    ];

    public function importedItem()
    {
        return $this->hasOne(CcdInsurancePolicy::class);
    }
}
