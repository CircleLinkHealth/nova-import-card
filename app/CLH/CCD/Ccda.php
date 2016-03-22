<?php namespace App\CLH\CCD;

use App\CLH\CCD\ItemLogger\ModelLogRelationship;
use Illuminate\Database\Eloquent\Model;

class Ccda extends Model
{

    use ModelLogRelationship;

    protected $guarded = [];

    public function qaSummary()
    {
		return $this->hasOne(QAImportSummary::class);
    }
}
