<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CareAmbassador extends \App\BaseModel
{

    protected $fillable = [
        'user_id',
        'hourly_rate',
        'speaks_spanish'
    ];

    public function user()
    {

        return $this->belongsTo(User::class, 'user_id');
    }

    public function logs()
    {

        return $this->hasMany(CareAmbassadorLog::class, 'enroller_id');
    }
}
