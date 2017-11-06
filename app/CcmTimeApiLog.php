<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class CcmTimeApiLog extends \App\BaseModel implements Transformable
{

    use TransformableTrait;

    protected $guarded = [];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
