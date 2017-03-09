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

    protected $guarded = [];

    public function ccdLog()
    {
        return $this->belongsTo(DemographicsLog::class);
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

}
