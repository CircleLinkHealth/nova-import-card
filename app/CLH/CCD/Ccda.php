<?php namespace App\CLH\CCD;

use App\CLH\CCD\ItemLogger\ModelLogRelationship;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Ccda extends Model implements Transformable{

    use ModelLogRelationship, TransformableTrait;

    //define sources here
    const API = 'api';
    const IMPORTER = 'importer';

    protected $guarded = [];

    public function qaSummary()
    {
		return $this->hasOne(QAImportSummary::class);
    }
}
