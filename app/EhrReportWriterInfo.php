<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EhrReportWriterInfo extends Model
{
    protected $fillable = [
        'drive_folder'
    ];

    public function careAmbassador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
