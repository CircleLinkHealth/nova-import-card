<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class EmailSettings extends \App\BaseModel
{
    public $fillable = [
        'user_id',
        'frequency'
    ];

    const DAILY = 'daily';
    const WEEKLY = 'weekly';
    const MWF = 'm/w/f';

    public $attributes = [
        'frequency' => EmailSettings::DAILY,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
