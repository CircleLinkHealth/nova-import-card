<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * These are IDs from third party systems.
 *
 * Example use:
 * XYZ CCD Vendor uses our API to submit CCDs and receive back reports and wants their system's id returned in the
 * response.
 *
 * Class ForeignId
 * @package App
 */
class ForeignId extends \App\BaseModel implements Transformable
{

    use TransformableTrait;

    protected $guarded = [];

    //Define systems here
    const APRIMA = 'aprima';
    const ATHENA = 'athena';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
