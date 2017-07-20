<?php

namespace App\Models;

use App\CarePlan;
use Illuminate\Database\Eloquent\Model;

class Pdf extends Model
{
    /**
     * Get all of the owning pdfable models.
     */
    public function pdfable()
    {
        return $this->morphTo();
    }
}
