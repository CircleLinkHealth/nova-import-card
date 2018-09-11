<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property \Carbon\Carbon $patient_created_at
 */
class CallView extends Model
{
    protected $table = 'calls_view';
}
