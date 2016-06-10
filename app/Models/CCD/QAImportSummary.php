<?php namespace App\Models\CCD;

use App\Models\CCD\Ccda;
use Illuminate\Database\Eloquent\Model;

class QAImportSummary extends Model {

    protected $guarded = [];

    public function ccda()
    {
        return $this->belongsTo(Ccda::class);
    }

}
