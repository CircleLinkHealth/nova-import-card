<?php namespace App\CLH\CCD\ItemLogger;

use App\Models\CCD\Ccda;
use App\CLH\CCD\ImportedItems\AllergyImport;
use App\CLH\CCD\ItemLogger\BelongsToCcda;
use App\CLH\CCD\ItemLogger\LogVendorRelationship;
use App\CLH\Contracts\CCD\CcdItemLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CcdAllergyLog extends Model implements CcdItemLog
{
    use BelongsToCcda, LogVendorRelationship;

    protected $guarded = [];

    public function importedItem()
    {
        return $this->hasOne(AllergyImport::class);
    }

}
