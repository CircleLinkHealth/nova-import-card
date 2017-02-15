<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Enrollee extends Model
{
    protected $table = 'enrollees';

    protected $fillable = [
        'id',
        'user_id',
        'provider_id',
        'practice_id',
        'mrn_number',
        'first_name',
        'last_name',
        'address',
        'invite_code',
        'phone',
        'consented_at',
        'last_attempt_at',
        'attempt_count',
        'preferred_window',
        'preferred_days',
        'status',
    ];

    public function user(){

        return $this->belongsTo(User::class, 'user_id');

    }

    public function provider(){

        return $this->belongsTo(User::class, 'provider_id');

    }

    public function practice(){

        return $this->belongsTo(Practice::class, 'practice_id');

    }

}
