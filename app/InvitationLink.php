<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class InvitationLink extends Model
{
  //  use SoftDeletes;

    protected $fillable = [
        'awv_user_id',
        'survey_id',
        'link_token',
        'is_manually_expired'
    ];

    protected $casts = [
        'is_manually_expired' => 'boolean'
    ];


    public function patient()
    {
        return $this->belongsTo(AwvPatients::class);
    }
}
