<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{

    protected $fillable = ['*'];

    public function patient(){

        return $this->belongsTo(PatientInfo::class, 'patient_id');

    }

    public function author(){

        return $this->belongsTo(User::class, 'author_id');

    }

    public function provider(){

        return $this->belongsTo(ProviderInfo::class, 'provider_id');

    }

}
