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
        return $this->morphedByMany(Practice::class, 'chargeable')
            ->withTimestamps();
    }

    public function providers(){
        return $this->morphedByMany(User::class, 'chargeable')
            ->withTimestamps();
    }
}
