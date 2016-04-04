<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CcmTimeApiLog extends Model {

	protected $guarded = [];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

}
