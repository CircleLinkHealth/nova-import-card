<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChargeableService extends Model
{

    protected $fillable = [
        'code',
        'description',
        'charge'
    ];

    public function practices(){
        return $this->morphedByMany(Practice::class, 'taggable');
    }

    public function providers(){
        return $this->morphedByMany(User::class, 'taggable');
    }
}
