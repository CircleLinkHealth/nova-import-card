<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkHours extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workhourable_type',
        'workhourable_id',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    /**
     * Get all of the owning workhourable models.
     */
    public function workhourable()
    {
        return $this->morphTo();
    }

}
