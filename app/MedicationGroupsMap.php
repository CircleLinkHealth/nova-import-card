<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedicationGroupsMap extends Model
{
    protected $fillable = [
        'keyword',
        'medication_group_id',
    ];
}
