<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvitationLink extends Model
{
  //  use SoftDeletes;

    protected $fillable = [
        'aw_patient_id',
        'survey_id',
        'link_token',
        'is_expired'
    ];
    //protected $dates = ['deleted_at'];
    protected $casts = [
        'is_expired' => 'boolean'
    ];
   // protected $table = 'invitation_links';

    public function patient()
    {
        return $this->belongsTo(awvPatients::class);
    }
}
