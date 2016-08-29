<?php namespace App\Models\CCD;

use App\CLH\CCD\ItemLogger\ModelLogRelationship;
use App\Models\CCD\QAImportSummary;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Ccda extends Model implements Transformable {

    use ModelLogRelationship, TransformableTrait;

    //define sources here
    const ATHENA_API = 'athena_api';
    const API = 'api';
    const IMPORTER = 'importer';

    protected $guarded = [];

    public function qaSummary()
    {
		return $this->hasOne(QAImportSummary::class);
    }
}
