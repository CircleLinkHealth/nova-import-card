<?php

namespace App\Models;

use App\CarePlan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pdf extends \App\BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'uploaded_by',
        'pdfable_type',
        'pdfable_id',
        'filename',
        'file',
    ];

    protected $hidden = ['file'];

    /**
     * Get all of the owning pdfable models.
     */
    public function pdfable()
    {
        return $this->morphTo();
    }
}
