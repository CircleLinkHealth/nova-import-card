<?php namespace App\CLH\CCD\ItemLogger;

use App\CLH\CCD\ImportedItems\AllergyImport;
use App\CLH\Contracts\CCD\HealthRecordSectionLog;
use Illuminate\Database\Eloquent\Model;

class CcdAllergyLog extends Model implements HealthRecordSectionLog
{
    use BelongsToCcda, LogVendorRelationship;

    protected $guarded = [];

    public function importedItem()
    {
        return $this->hasOne(AllergyImport::class);
    }

}
