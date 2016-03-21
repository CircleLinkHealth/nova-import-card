<?php namespace App\CLH\CCD;

use Illuminate\Database\Eloquent\Model;

class QAImportSummary extends Model {

    protected $guarded = [];

    public function ccda()
    {
        return $this->belongsTo(Ccda::class);
    }

}
