<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{

    protected $table = 'appointments';

    protected $fillable = [
        'patient_id',
        'author_id',
        'provider_id',
        'date',
        'time',
        'comment',
        'created_at',
        'updated_at'
    ];

    public function patient(){

        return $this->belongsTo(User::class, 'patient_id');

    }

    public function author(){

        return $this->belongsTo(User::class, 'author_id');

    }

    public function provider(){

        return $this->belongsTo(User::class, 'provider_id');

    }

}
