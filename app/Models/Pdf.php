<?php

namespace App\Models;

use App\CarePlan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pdf extends Model
{
    use SoftDeletes;

    /**
     * Get all of the owning pdfable models.
     */
    public function pdfable()
    {
        return $this->morphTo();
    }
}
