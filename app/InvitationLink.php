<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class InvitationLink extends Model
{
  //  use SoftDeletes;

    protected $fillable = [
        'awv_patient_id',
        'survey_id',
        'link_token',
        'is_expired'
    ];

    protected $casts = [
        'is_expired' => 'boolean'
    ];


    public function patient()
    {
        return $this->belongsTo(AwvPatients::class);
    }
}
