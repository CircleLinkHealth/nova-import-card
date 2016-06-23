<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{

    protected $table = 'calls';

    protected $fillable = [
        'note_id',
        'service',
        'status',

        'inbound_phone_number',
        'outbound_phone_number',

        'inbound_cpm_id',
        'outbound_cpm_id',

        'call_time',
        'created_at',

        'is_cpm_outbound'
    ];

    public function note()
    {
        return $this->belongsTo('App\Note', 'note_id', 'id');
    }


}
