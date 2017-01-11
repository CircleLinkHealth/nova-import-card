<?php namespace App\Models\MedicalRecords;

use Illuminate\Database\Eloquent\Model;

class ImportedMedicalRecord extends Model
{

    protected $table = 'q_a_import_summaries';

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
