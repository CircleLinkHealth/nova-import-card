<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EnrolleeCustomFilter extends Model
{
    protected $fillable = [
        'name',
        'type',
    ];

    public function practices(){
        return $this->belongsToMany(Practice::class, 'practice_enrollee_filters', 'filter_id', 'practice_id')
            ->withPivot('include');
    }
}
