<?php namespace App\Models\CCD;

use Illuminate\Database\Eloquent\Model;

class QAImportSummary extends Model {

    protected $fillable = [
        'ccda_id',
        'flag',
        'duplicate_id',
        'name',
        'provider',
        'location',
        'hasName',
        'medications',
        'problems',
        'allergies',
        'has_phone',
    ];

    public function ccda()
    {
        return $this->belongsTo(\App\Models\MedicalRecords\Ccda::class);
    }

}
